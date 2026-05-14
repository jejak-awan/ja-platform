import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import Index from '@/modules/School/views/admin/students/Index.vue';
import api from '@/engine/api/client';
import { createRouter, createWebHistory } from 'vue-router';

// Mock services
vi.mock('@/engine/api/client');
vi.mock('@/shared/composables/useToast', () => ({
    useToast: () => ({
        success: { action: vi.fn() },
        error: { fromResponse: vi.fn() }
    })
}));
vi.mock('@/shared/composables/useConfirm', () => ({
    useConfirm: () => ({
        confirm: vi.fn(() => Promise.resolve(true))
    })
}));

// Mock UI components to avoid deep rendering issues
vi.mock('@/shared/components/ui', () => ({
    Card: { template: '<div><slot /></div>' },
    CardContent: { template: '<div><slot /></div>' },
    Button: { template: '<button><slot /></button>' },
    LucideIcon: { template: '<span>Icon</span>' },
    DataTable: { template: '<div class="data-table">Table</div>' },
    Pagination: { template: '<div class="pagination">Pagination</div>' }
}));

const router = createRouter({
    history: createWebHistory(),
    routes: [{ path: '/', name: 'students.index', component: { template: '<div></div>' } }]
});

describe('Student Index Component', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('fetches and displays student data on mount', async () => {
        const mockResponse = {
            data: {
                data: [
                    { id: 1, nisn: '1234567890', full_name: 'John Doe', gender: 'L', level: { name: 'High' } }
                ],
                total: 1,
                per_page: 20,
                current_page: 1
            }
        };

        vi.mocked(api.get).mockResolvedValue(mockResponse);

        const wrapper = mount(Index, {
            global: {
                plugins: [router],
                stubs: {
                    'router-link': true
                }
            }
        });

        // Wait for onMounted and fetchStudents
        await vi.dynamicImportSettled();
        await new Promise(resolve => setTimeout(resolve, 0));

        expect(api.get).toHaveBeenCalledWith('/admin/students?page=1');
        // Use wrapper here to avoid unused variable warning
        expect(wrapper.find('h1').text()).toBe('Data Siswa');
    });

    it('handles delete request correctly', async () => {
        const mockData = [
            { id: 1, nisn: '1234567890', full_name: 'John Doe', gender: 'L', level: { name: 'High' } }
        ];

        vi.mocked(api.get).mockResolvedValue({
            data: { data: mockData, total: 1, per_page: 20, current_page: 1 }
        });
        vi.mocked(api.delete).mockResolvedValue({ data: { success: true } });

        const wrapper = mount(Index, {
            global: {
                plugins: [router],
                stubs: { 'router-link': true }
            }
        });

        await vi.dynamicImportSettled();

        // Trigger handleDelete directly via VM since we mocked the button/icon inside DataTable
        await (wrapper.vm as any).handleDelete(1);

        expect(api.delete).toHaveBeenCalledWith('/admin/students/1');
    });
});
