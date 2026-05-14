import api from '@/engine/api/client';

export interface Course {
  id: number;
  title: string;
  slug: string;
  summary: string;
  description: string;
  image_path: string | null;
  level: 'beginner' | 'intermediate' | 'advanced';
  status: 'draft' | 'published' | 'archived';
  lessons_count?: number;
  academic_year_id?: number;
  semester_id?: number;
  department_id?: number;
  grade_id?: number;
  academic_year?: { id: number, year: string };
  semester?: { id: number, semester: string };
  department?: { id: number, name: string };
  grade?: { id: number, name: string };
}

export interface Topic {
  id: number;
  title: string;
  order: number;
  topicable_type: string;
  topicable: any;
}

export interface Lesson {
  id: number;
  title: string;
  summary: string;
  topics: Topic[];
}

const LmsService = {
  // Admin Endpoints
  getAdminCourses() {
    return api.get('/admin/lms/courses');
  },
  
  getCourseDetails(id: number) {
    return api.get(`/admin/lms/courses/${id}`);
  },

  createCourse(data: any) {
    return api.post('/admin/lms/courses', data);
  },

  createLesson(courseId: number, data: any) {
    return api.post(`/admin/lms/courses/${courseId}/lessons`, data);
  },

  createTopic(lessonId: number, data: any) {
    return api.post(`/admin/lms/lessons/${lessonId}/topics`, data);
  },

  addQuestion(quizId: number, data: any) {
    return api.post(`/admin/lms/quizzes/${quizId}/questions`, data);
  },

  // Student Endpoints
  getCatalog() {
    return api.get('/student/lms/catalog');
  },

  getMyCourses() {
    return api.get('/student/lms/my-courses');
  },

  getLearningData(courseId: number) {
    return api.get(`/student/lms/courses/${courseId}/learn`);
  },

  completeTopic(topicId: number, metadata: any = {}) {
    return api.post(`/student/lms/topics/${topicId}/complete`, { metadata });
  },

  startQuiz(quizId: number) {
    return api.post(`/student/lms/quizzes/${quizId}/start`);
  },

  submitQuiz(attemptId: number, answers: any) {
    return api.post(`/student/lms/attempts/${attemptId}/submit`, { answers });
  }
};

export default LmsService;
