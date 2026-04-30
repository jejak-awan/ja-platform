import { defineStore } from 'pinia';
import LmsService from '../services/LmsService';
import type { Course } from '../services/LmsService';

export const useLmsStore = defineStore('lms', {
  state: () => ({
    courses: [] as Course[],
    currentCourse: null as any,
    catalog: [] as Course[],
    myCourses: [] as Course[],
    loading: false,
    error: null as string | null,
  }),

  actions: {
    async fetchAdminCourses() {
      this.loading = true;
      try {
        const response = await LmsService.getAdminCourses();
        this.courses = response.data;
      } catch (err: any) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchCatalog() {
      this.loading = true;
      try {
        const response = await LmsService.getCatalog();
        this.catalog = response.data;
      } catch (err: any) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchMyCourses() {
      this.loading = true;
      try {
        const response = await LmsService.getMyCourses();
        this.myCourses = response.data;
      } catch (err: any) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchCourseDetails(id: number) {
      this.loading = true;
      try {
        const response = await LmsService.getCourseDetails(id);
        this.currentCourse = response.data;
        return response.data;
      } catch (err: any) {
        this.error = err.message;
        throw err;
      } finally {
        this.loading = false;
      }
    }
  }
});
