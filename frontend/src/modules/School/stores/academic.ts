import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';
import AcademicService from '../services/AcademicService';
import type { AcademicYear, Semester, Subject, StudyGroup, Schedule, Journal, AcademicOverview } from '@/modules/School/types';

interface AcademicState {
    overview: AcademicOverview | null;
    years: AcademicYear[];
    semesters: Semester[];
    subjects: Subject[];
    studyGroups: StudyGroup[];
    schedules: Schedule[];
    journals: Journal[];
    loading: boolean;
}

export const useAcademicStore = defineStore('academic', {
    state: (): AcademicState => ({
        overview: null,
        years: [],
        semesters: [],
        subjects: [],
        studyGroups: [],
        schedules: [],
        journals: [],
        loading: false,
    }),

    actions: {
        async fetchOverview() {
            this.loading = true;
            try {
                const response = await AcademicService.getOverview();
                this.overview = parseSingleResponse<AcademicOverview>(response);
            } catch (e) {
                logger.error('Failed to fetch academic overview:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchAcademicYears() {
            this.loading = true;
            try {
                const response = await AcademicService.getAcademicYears();
                const { data } = parseResponse<AcademicYear>(response);
                this.years = data;
            } catch (e) {
                logger.error('Failed to fetch academic years:', e);
            } finally {
                this.loading = false;
            }
        },

        async deleteAcademicYear(id: string) {
            try {
                await AcademicService.deleteAcademicYear(id);
                this.years = this.years.filter(y => y.id !== id);
            } catch (e) {
                logger.error('Failed to delete academic year:', e);
                throw e;
            }
        },

        async fetchSubjects() {
            this.loading = true;
            try {
                const response = await AcademicService.getSubjects();
                const { data } = parseResponse<Subject>(response);
                this.subjects = data;
            } catch (e) {
                logger.error('Failed to fetch subjects:', e);
            } finally {
                this.loading = false;
            }
        },

        async deleteSubject(id: string) {
            try {
                await AcademicService.deleteSubject(id);
                this.subjects = this.subjects.filter(s => s.id !== id);
            } catch (e) {
                logger.error('Failed to delete subject:', e);
                throw e;
            }
        },

        async saveAcademicYear(data: Partial<AcademicYear>, id: string | null = null) {
            try {
                if (id) await AcademicService.updateAcademicYear(id, data);
                else await AcademicService.storeAcademicYear(data);
                await this.fetchAcademicYears();
            } catch (e) {
                logger.error('Failed to save academic year:', e);
                throw e;
            }
        },

        async saveSubject(data: Partial<Subject>, id: string | null = null) {
            try {
                if (id) await AcademicService.updateSubject(id, data);
                else await AcademicService.storeSubject(data);
                await this.fetchSubjects();
            } catch (e) {
                logger.error('Failed to save subject:', e);
                throw e;
            }
        },

        async saveStudyGroup(data: Partial<StudyGroup>, id: string | null = null) {
            try {
                if (id) await AcademicService.updateStudyGroup(id, data);
                else await AcademicService.storeStudyGroup(data);
                await this.fetchStudyGroups();
            } catch (e) {
                logger.error('Failed to save study group:', e);
                throw e;
            }
        },

        async fetchStudyGroups() {
            this.loading = true;
            try {
                const response = await AcademicService.getStudyGroups();
                const { data } = parseResponse<StudyGroup>(response);
                this.studyGroups = data;
            } catch (e) {
                logger.error('Failed to fetch study groups:', e);
            } finally {
                this.loading = false;
            }
        },

        async deleteStudyGroup(id: string) {
            try {
                await AcademicService.deleteStudyGroup(id);
                this.studyGroups = this.studyGroups.filter(g => g.id !== id);
            } catch (e) {
                logger.error('Failed to delete study group:', e);
                throw e;
            }
        },

        async fetchSchedules(params = {}) {
            this.loading = true;
            try {
                const response = await AcademicService.getSchedules(params);
                const { data } = parseResponse<Schedule>(response);
                this.schedules = data;
            } catch (e) {
                logger.error('Failed to fetch schedules:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
