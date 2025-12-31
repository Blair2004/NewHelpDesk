import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import('../layouts/PublicLayout.vue'),
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('../pages/Home.vue'),
            },
            {
                path: 'login',
                name: 'login',
                component: () => import('../pages/Login.vue'),
            },
        ],
    },
    {
        path: '/dashboard',
        component: () => import('../layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../pages/Dashboard.vue'),
            },
            {
                path: 'threads',
                name: 'threads',
                component: () => import('../pages/Threads.vue'),
            },
            {
                path: 'threads/:id',
                name: 'thread-detail',
                component: () => import('../pages/ThreadDetail.vue'),
            },
            {
                path: 'departments',
                name: 'departments',
                component: () => import('../pages/Departments.vue'),
                meta: { requiresStaff: true },
            },
            {
                path: 'customers',
                name: 'customers',
                component: () => import('../pages/Customers.vue'),
                meta: { requiresStaff: true },
            },
            {
                path: 'reports',
                name: 'reports',
                component: () => import('../pages/Reports.vue'),
                meta: { requiresStaff: true },
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../pages/Settings.vue'),
                meta: { requiresStaff: true },
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../pages/Profile.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    // Lazy import to avoid circular dependencies
    const { useAuthStore } = await import('../stores/auth');
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.requiresStaff && !authStore.user?.is_staff) {
        next({ name: 'dashboard' });
    } else if (to.name === 'dashboard' && authStore.isAuthenticated && !authStore.user?.is_staff) {
        // Redirect non-staff users to their threads page
        next({ name: 'threads' });
    } else {
        next();
    }
});

export default router;
