import axios from 'axios';

// Inertia running on the same Laravel origin can use Sanctum's stateful cookie auth.
// For mobile/token clients, set the Authorization Bearer header in your existing API client.
export const subscriptionApi = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    withXSRFToken: true,
    headers: { Accept: 'application/json' },
});

export type SubscriptionPlan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    currency: string;
    billing_cycle: string;
    duration_days: number;
    user_type: string;
    features: Record<string, { enabled: boolean; limit?: number }>;
};

export const getSubscriptionPlans = () => subscriptionApi.get('/subscription-plans');
export const getCurrentSubscription = () => subscriptionApi.get('/subscriptions/current');
export const getSubscriptionHistory = (page = 1) => subscriptionApi.get(`/subscriptions/history?page=${page}`);
export const getSubscriptionPayments = (page = 1) => subscriptionApi.get(`/subscription-payments?page=${page}`);
export const startSubscriptionCheckout = (planId: number) =>
    subscriptionApi.post('/subscriptions/checkout', { plan_id: planId });
export const activateFreeSubscription = (planId: number) =>
    subscriptionApi.post('/subscriptions/free-activate', { plan_id: planId });
export const renewSubscription = (subscriptionId: number) =>
    subscriptionApi.post(`/subscriptions/${subscriptionId}/renew`);
