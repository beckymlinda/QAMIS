import * as pdfjsLib from 'pdfjs-dist';
import { PDFDocument, rgb } from 'pdf-lib';

pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf/pdf.worker.min.mjs';

const PEN_COLOR = '#dc2626';
const PEN_WIDTH = 2.5;
const DEFAULT_ZOOM = 1.25;

function hexToRgb(hex) {
    const value = hex.replace('#', '');
    return rgb(
        parseInt(value.slice(0, 2), 16) / 255,
        parseInt(value.slice(2, 4), 16) / 255,
        parseInt(value.slice(4, 6), 16) / 255,
    );
}

function parseExisting(raw) {
    if (!raw) {
        return { pages: {} };
    }
    try {
        const data = JSON.parse(raw);
        if (data.pages) {
            return data;
        }
        if (Array.isArray(data)) {
            return { pages: { 0: { strokes: data } } };
        }
    } catch {
        // ignore invalid JSON
    }
    return { pages: {} };
}

function initPdfGrader(root) {
    const pdfUrl = root.dataset.pdfUrl;
    const form = document.querySelector('[data-grade-form]');
    const annotationInput = document.querySelector('[data-annotation-input]');
    const markedFileInput = document.querySelector('[data-marked-file-input]');
    const viewport = root.querySelector('[data-pdf-viewport]');
    const pageLabel = root.querySelector('[data-page-label]');
    const btnPen = root.querySelector('[data-tool-pen]');
    const btnZoomIn = root.querySelector('[data-zoom-in]');
    const btnZoomOut = root.querySelector('[data-zoom-out]');
    const btnZoomReset = root.querySelector('[data-zoom-reset]');
    const btnUndo = root.querySelector('[data-undo]');
    const btnClear = root.querySelector('[data-clear-page]');
    const btnPrev = root.querySelector('[data-page-prev]');
    const btnNext = root.querySelector('[data-page-next]');

    if (!pdfUrl || !viewport || !form) {
        return;
    }

    let pdfDoc = null;
    let pageCount = 0;
    let currentPage = 1;
    let zoom = DEFAULT_ZOOM;
    let tool = 'pen';
    let isDrawing = false;
    let activeStroke = null;
    let annotationData = parseExisting(root.dataset.existingAnnotations);
    let pageMetrics = {};
    let pageAbortController = null;

    const updateZoomLabel = () => {
        if (btnZoomReset) {
            btnZoomReset.textContent = `${Math.round(zoom * 100)}%`;
        }
    };

    const setActiveTool = (next) => {
        tool = next;
        btnPen?.classList.toggle('bg-[#0f2744]', tool === 'pen');
        btnPen?.classList.toggle('text-white', tool === 'pen');
    };

    const strokesForPage = (pageIndex) => {
        const key = String(pageIndex);
        if (!annotationData.pages[key]) {
            annotationData.pages[key] = { strokes: [] };
        }
        return annotationData.pages[key].strokes;
    };

    const canvasToPdf = (x, y, pageIndex) => {
        const metrics = pageMetrics[pageIndex];
        if (!metrics) {
            return { x: 0, y: 0 };
        }
        return {
            x: (x / metrics.width) * metrics.pdfWidth,
            y: metrics.pdfHeight - (y / metrics.height) * metrics.pdfHeight,
        };
    };

    const pdfToCanvas = (x, y, pageIndex) => {
        const metrics = pageMetrics[pageIndex];
        if (!metrics) {
            return { x: 0, y: 0 };
        }
        return {
            x: (x / metrics.pdfWidth) * metrics.width,
            y: ((metrics.pdfHeight - y) / metrics.pdfHeight) * metrics.height,
        };
    };

    const drawStroke = (ctx, stroke, pageIndex) => {
        if (!stroke.points?.length) {
            return;
        }
        ctx.strokeStyle = stroke.color || PEN_COLOR;
        ctx.lineWidth = stroke.width || PEN_WIDTH;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();
        stroke.points.forEach((point, index) => {
            const { x, y } = pdfToCanvas(point[0], point[1], pageIndex);
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.stroke();
    };

    const redrawAnnotations = (annotateCanvas, pageIndex) => {
        const ctx = annotateCanvas.getContext('2d');
        ctx.clearRect(0, 0, annotateCanvas.width, annotateCanvas.height);
        strokesForPage(pageIndex).forEach((stroke) => drawStroke(ctx, stroke, pageIndex));
        if (activeStroke && activeStroke.pageIndex === pageIndex) {
            drawStroke(ctx, activeStroke, pageIndex);
        }
    };

    const cleanupPageListeners = () => {
        pageAbortController?.abort();
        pageAbortController = null;
    };

    const renderPage = async () => {
        cleanupPageListeners();
        pageAbortController = new AbortController();
        const { signal } = pageAbortController;

        viewport.innerHTML = '<p class="p-6 text-sm text-gray-500">Loading page…</p>';

        const pageIndex = currentPage - 1;
        const page = await pdfDoc.getPage(currentPage);
        const baseViewport = page.getViewport({ scale: zoom });
        const pdfWidth = page.view[2] - page.view[0];
        const pdfHeight = page.view[3] - page.view[1];

        pageMetrics[pageIndex] = {
            width: baseViewport.width,
            height: baseViewport.height,
            pdfWidth,
            pdfHeight,
        };

        viewport.innerHTML = '';

        const wrapper = document.createElement('div');
        wrapper.className = 'relative inline-block shadow-md';
        wrapper.style.width = `${baseViewport.width}px`;
        wrapper.style.height = `${baseViewport.height}px`;

        const pdfCanvas = document.createElement('canvas');
        pdfCanvas.width = baseViewport.width;
        pdfCanvas.height = baseViewport.height;
        pdfCanvas.className = 'block bg-white';

        const annotateCanvas = document.createElement('canvas');
        annotateCanvas.width = baseViewport.width;
        annotateCanvas.height = baseViewport.height;
        annotateCanvas.className = 'absolute inset-0';
        annotateCanvas.style.cursor = tool === 'pen' ? 'crosshair' : 'default';
        annotateCanvas.style.touchAction = 'pan-x pan-y';

        wrapper.append(pdfCanvas, annotateCanvas);
        viewport.append(wrapper);

        await page.render({
            canvasContext: pdfCanvas.getContext('2d'),
            viewport: baseViewport,
        }).promise;

        redrawAnnotations(annotateCanvas, pageIndex);

        const localPoint = (event) => {
            const rect = annotateCanvas.getBoundingClientRect();
            const scaleX = annotateCanvas.width / rect.width;
            const scaleY = annotateCanvas.height / rect.height;
            return {
                x: (event.clientX - rect.left) * scaleX,
                y: (event.clientY - rect.top) * scaleY,
            };
        };

        const finishStroke = () => {
            if (!isDrawing || !activeStroke) {
                return;
            }
            isDrawing = false;
            annotateCanvas.style.touchAction = 'pan-x pan-y';
            if (activeStroke.points.length > 1) {
                strokesForPage(pageIndex).push({
                    tool: activeStroke.tool,
                    color: activeStroke.color,
                    width: activeStroke.width,
                    points: activeStroke.points,
                });
            }
            activeStroke = null;
            redrawAnnotations(annotateCanvas, pageIndex);
        };

        const pointerDown = (event) => {
            if (tool !== 'pen' || event.button !== 0) {
                return;
            }
            event.preventDefault();
            annotateCanvas.setPointerCapture(event.pointerId);
            annotateCanvas.style.touchAction = 'none';
            isDrawing = true;
            const { x, y } = localPoint(event);
            const pdfPoint = canvasToPdf(x, y, pageIndex);
            activeStroke = {
                tool: 'pen',
                color: PEN_COLOR,
                width: PEN_WIDTH,
                pageIndex,
                points: [[pdfPoint.x, pdfPoint.y]],
            };
        };

        const pointerMove = (event) => {
            if (!isDrawing || !activeStroke) {
                return;
            }
            event.preventDefault();
            const { x, y } = localPoint(event);
            const pdfPoint = canvasToPdf(x, y, pageIndex);
            const points = activeStroke.points;
            const last = points[points.length - 1];
            if (Math.hypot(pdfPoint.x - last[0], pdfPoint.y - last[1]) < 0.5) {
                return;
            }
            points.push([pdfPoint.x, pdfPoint.y]);
            redrawAnnotations(annotateCanvas, pageIndex);
        };

        const pointerUp = (event) => {
            if (!isDrawing) {
                return;
            }
            if (annotateCanvas.hasPointerCapture(event.pointerId)) {
                annotateCanvas.releasePointerCapture(event.pointerId);
            }
            finishStroke();
        };

        annotateCanvas.addEventListener('pointerdown', pointerDown, { signal });
        annotateCanvas.addEventListener('pointermove', pointerMove, { signal });
        annotateCanvas.addEventListener('pointerup', pointerUp, { signal });
        annotateCanvas.addEventListener('pointercancel', pointerUp, { signal });
        annotateCanvas.addEventListener('lostpointercapture', finishStroke, { signal });

        annotateCanvas.addEventListener('wheel', (event) => {
            viewport.scrollTop += event.deltaY;
            viewport.scrollLeft += event.deltaX;
        }, { signal, passive: true });

        if (pageLabel) {
            pageLabel.textContent = `Page ${currentPage} of ${pageCount}`;
        }
        if (btnPrev) btnPrev.disabled = currentPage <= 1;
        if (btnNext) btnNext.disabled = currentPage >= pageCount;
        updateZoomLabel();
    };

    const loadPdf = async () => {
        viewport.innerHTML = '<p class="p-6 text-sm text-gray-500">Loading PDF…</p>';
        const loadingTask = pdfjsLib.getDocument({ url: pdfUrl, withCredentials: true });
        pdfDoc = await loadingTask.promise;
        pageCount = pdfDoc.numPages;
        await renderPage();
    };

    btnPen?.addEventListener('click', () => setActiveTool('pen'));
    btnZoomIn?.addEventListener('click', async () => {
        zoom = Math.min(zoom + 0.25, 3);
        await renderPage();
    });
    btnZoomOut?.addEventListener('click', async () => {
        zoom = Math.max(zoom - 0.25, 0.5);
        await renderPage();
    });
    btnZoomReset?.addEventListener('click', async () => {
        zoom = DEFAULT_ZOOM;
        await renderPage();
    });
    btnUndo?.addEventListener('click', async () => {
        const strokes = strokesForPage(currentPage - 1);
        strokes.pop();
        await renderPage();
    });
    btnClear?.addEventListener('click', async () => {
        annotationData.pages[String(currentPage - 1)] = { strokes: [] };
        await renderPage();
    });
    btnPrev?.addEventListener('click', async () => {
        if (currentPage > 1) {
            currentPage -= 1;
            await renderPage();
        }
    });
    btnNext?.addEventListener('click', async () => {
        if (currentPage < pageCount) {
            currentPage += 1;
            await renderPage();
        }
    });

    form.addEventListener('submit', async (event) => {
        if (annotationInput) {
            annotationInput.value = JSON.stringify(annotationData);
        }

        const manualInput = document.getElementById('manual-marked-file');
        if (manualInput?.files?.length && markedFileInput) {
            markedFileInput.files = manualInput.files;
            return;
        }

        const hasStrokes = Object.values(annotationData.pages).some((page) => page.strokes?.length);
        if (!markedFileInput || !hasStrokes) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Saving marked PDF…';
        }

        try {
            const existingPdfBytes = await fetch(pdfUrl, { credentials: 'same-origin' }).then((res) => {
                if (!res.ok) {
                    throw new Error(`Could not fetch PDF (${res.status})`);
                }
                return res.arrayBuffer();
            });
            const markedDoc = await PDFDocument.load(existingPdfBytes);
            const pages = markedDoc.getPages();

            Object.entries(annotationData.pages).forEach(([pageKey, pageData]) => {
                const pageIndex = Number(pageKey);
                const pdfPage = pages[pageIndex];
                if (!pdfPage || !pageData.strokes) {
                    return;
                }
                pageData.strokes.forEach((stroke) => {
                    const color = hexToRgb(stroke.color || PEN_COLOR);
                    const width = stroke.width || PEN_WIDTH;
                    for (let i = 1; i < stroke.points.length; i += 1) {
                        const start = stroke.points[i - 1];
                        const end = stroke.points[i];
                        pdfPage.drawLine({
                            start: { x: start[0], y: start[1] },
                            end: { x: end[0], y: end[1] },
                            thickness: width,
                            color,
                        });
                    }
                });
            });

            const pdfBytes = await markedDoc.save();
            const file = new File([pdfBytes], 'marked-submission.pdf', { type: 'application/pdf' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            markedFileInput.files = dataTransfer.files;
        } catch (error) {
            console.error('Failed to generate marked PDF', error);
            alert('Could not embed pen marks into the PDF. Your annotations were saved as data — try again or upload a marked PDF manually.');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Save grade';
            }
            return;
        }

        form.submit();
    });

    setActiveTool('pen');
    updateZoomLabel();
    loadPdf().catch((error) => {
        console.error('Failed to load PDF', error);
        viewport.innerHTML = '<p class="p-4 text-sm text-red-600">Could not load PDF preview. Use Download original instead.</p>';
    });
}

function bootPdfGraders() {
    document.querySelectorAll('[data-lms-pdf-grader]').forEach(initPdfGrader);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPdfGraders);
} else {
    bootPdfGraders();
}
