<?php

namespace Database\Seeders;

use App\Models\EvaluationQuestion;
use App\Models\EvaluationQuestionCategory;
use Illuminate\Database\Seeder;

class EvaluationQuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'course' => [
                ['code' => 'course_organization', 'title' => 'Course Organization', 'questions' => [
                    'The course was well organized throughout the semester.',
                    'The course schedule and activities were clearly communicated.',
                    'The topics were presented in a logical sequence.',
                ]],
                ['code' => 'clarity_learning_outcomes', 'title' => 'Clarity of Learning Outcomes', 'questions' => [
                    'The learning outcomes of the course were clearly explained.',
                    'I understood what was expected of me in this course.',
                    'The teaching and assessments aligned with the stated learning outcomes.',
                ]],
                ['code' => 'relevance_course_content', 'title' => 'Relevance of Course Content', 'questions' => [
                    'The course content was relevant to my programme of study.',
                    'The knowledge and skills taught apply to real-world situations or my future career.',
                    'The course content reflected current developments in the field.',
                ]],
                ['code' => 'quality_teaching_materials', 'title' => 'Quality of Teaching Materials', 'questions' => [
                    'The teaching materials (slides, notes, handouts, or online resources) were of high quality.',
                    'Learning resources were readily available and useful.',
                    'The teaching materials supported my understanding of the course.',
                ]],
                ['code' => 'assessment_methods', 'title' => 'Assessment Methods', 'questions' => [
                    'The assessment methods adequately measured my learning.',
                    'Assessment instructions and grading criteria were clear.',
                    'Feedback on assessments was helpful for improving my learning.',
                ]],
                ['code' => 'workload_appropriateness', 'title' => 'Workload Appropriateness', 'questions' => [
                    'The workload for this course was appropriate for the credit hours assigned.',
                    'The pace of the course was manageable.',
                    'The assignments and activities were balanced throughout the semester.',
                ]],
                ['code' => 'course_overall_satisfaction', 'title' => 'Overall Satisfaction (Course)', 'questions' => [
                    'Overall, I am satisfied with the quality of this course.',
                    'I would recommend this course to other students.',
                    'This course contributed positively to my academic development.',
                ]],
            ],
            'lecturer' => [
                ['code' => 'subject_knowledge', 'title' => 'Subject Knowledge', 'questions' => [
                    'The lecturer demonstrated thorough knowledge of the subject matter.',
                    'The lecturer answered students\' questions accurately and confidently.',
                ]],
                ['code' => 'teaching_effectiveness', 'title' => 'Teaching Effectiveness', 'questions' => [
                    'The lecturer explained concepts clearly and effectively.',
                    'The lecturer used appropriate teaching methods to facilitate learning.',
                    'The lecturer helped me understand difficult concepts.',
                ]],
                ['code' => 'communication_skills', 'title' => 'Communication Skills', 'questions' => [
                    'The lecturer communicated ideas clearly and understandably.',
                    'The lecturer encouraged questions and discussion during class.',
                ]],
                ['code' => 'class_preparedness', 'title' => 'Class Preparedness', 'questions' => [
                    'The lecturer appeared well prepared for each class session.',
                    'Classes were conducted in a structured and organized manner.',
                ]],
                ['code' => 'student_engagement', 'title' => 'Student Engagement', 'questions' => [
                    'The lecturer encouraged active participation from students.',
                    'The lecturer created an inclusive and supportive learning environment.',
                    'The lecturer motivated students to think critically and participate.',
                ]],
                ['code' => 'fairness_assessment', 'title' => 'Fairness in Assessment', 'questions' => [
                    'The lecturer assessed students fairly and consistently.',
                    'Grading was based on transparent and objective criteria.',
                ]],
                ['code' => 'professionalism', 'title' => 'Professionalism', 'questions' => [
                    'The lecturer treated students with respect and courtesy.',
                    'The lecturer maintained high standards of professional conduct.',
                    'The lecturer was punctual and adhered to scheduled class times.',
                ]],
                ['code' => 'availability_consultation', 'title' => 'Availability for Consultation', 'questions' => [
                    'The lecturer was accessible for academic consultation when needed.',
                    'The lecturer responded to student queries in a timely manner.',
                ]],
                ['code' => 'lecturer_overall_performance', 'title' => 'Overall Performance (Lecturer)', 'questions' => [
                    'Overall, the lecturer performed effectively in delivering this course.',
                    'I am satisfied with the lecturer\'s overall teaching performance.',
                    'I would be happy to take another course taught by this lecturer.',
                ]],
            ],
            'open' => [
                ['code' => 'open_comments', 'title' => 'Open-Ended Questions', 'questions' => [
                    'What aspects of this course did you find most beneficial?',
                    'What improvements would you suggest for this course?',
                    'What are the lecturer\'s greatest strengths?',
                    'What specific suggestions would you make to improve the lecturer\'s teaching?',
                    'Do you have any additional comments regarding the course or the lecturer?',
                ]],
            ],
        ];

        $sortOrder = 0;
        foreach ($sections as $section => $categories) {
            foreach ($categories as $categoryData) {
                $category = EvaluationQuestionCategory::updateOrCreate(
                    ['code' => $categoryData['code']],
                    [
                        'section' => $section,
                        'title' => $categoryData['title'],
                        'sort_order' => ++$sortOrder,
                    ]
                );

                foreach ($categoryData['questions'] as $index => $questionText) {
                    EvaluationQuestion::updateOrCreate(
                        [
                            'evaluation_question_category_id' => $category->id,
                            'sequence_no' => $index + 1,
                        ],
                        [
                            'question_text' => $questionText,
                            'question_type' => $section === 'open' ? 'text' : 'likert_5',
                        ]
                    );
                }
            }
        }
    }
}
