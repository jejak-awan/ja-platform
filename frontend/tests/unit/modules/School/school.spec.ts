import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useSchoolStore } from '@/modules/School/stores/school';
import api from '@/services/api';

vi.mock('@/services/api');

describe('School Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('fetches schools and updates state correctly', async () => {
        const store = useSchoolStore();
        const mockData = {
            success: true,
            data: {
                data: [
                    { id: 1, name: 'School A', type: 'public', level: 'Elementary', status: 'active' },
                    { id: 2, name: 'School B', type: 'private', level: 'Junior High', status: 'active' }
                ],
                current_page: 1,
                last_page: 1,
                per_page: 20,
                total: 2
            },
            message: 'Schools retrieved successfully'
        };

        vi.mocked(api.get).mockResolvedValue({
            data: mockData.data
        });

        await store.fetchSchools();

        expect(store.schools).toHaveLength(2);
        expect(store.schools[0]!.name).toBe('School A');
        expect(store.pagination!.total).toBe(2);
        expect(store.loading).toBe(false);
    });

    it('handles fetch single school correctly', async () => {
        const store = useSchoolStore();
        const mockSchool = { id: 1, name: 'School A', type: 'public', level: 'Elementary', status: 'active' };

        vi.mocked(api.get).mockResolvedValue({
            data: mockSchool
        });

        await store.fetchSchool(1);

        expect(store.currentSchool).toEqual(mockSchool);
        expect(store.loading).toBe(false);
    });

    it('handles delete school correctly', async () => {
        const store = useSchoolStore();
        store.schools = [
            { id: 1, name: 'School A', type: 'public', level: 'Elementary', status: 'active' }, 
            { id: 2, name: 'School B', type: 'private', level: 'Junior High', status: 'active' }
        ];

        vi.mocked(api.delete).mockResolvedValue({ data: { success: true } });

        await store.deleteSchool(1);

        expect(store.schools).toHaveLength(1);
        expect(store.schools[0]!.id).toBe(2);
    });
});
