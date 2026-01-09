import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || 'http:
const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            const status = error.response.status;
            if (status >= 500) {
                error.userMessage = 'Server error. Please try again later.';
                error.isServerError = true;
            } else if (status === 404) {
                error.userMessage = 'Resource not found.';
            } else if (status === 401) {
                error.userMessage = 'Unauthorized. Please login again.';
            } else if (status === 403) {
                error.userMessage = 'Access forbidden.';
            } else if (status >= 400) {
                error.userMessage = error.response.data?.message || 'Request failed. Please check your input.';
            }
        } else if (error.request) {
            error.userMessage = 'Cannot connect to server. Please check your internet connection.';
            error.isNetworkError = true;
        } else {
            error.userMessage = 'An unexpected error occurred.';
        }
        return Promise.reject(error);
    }
);
export const getPlantTypes = async () => {
    const response = await api.get('/plant-types');
    return response.data;
};
export const uploadScan = async (formData) => {
    const response = await api.post('/scan', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });
    return response.data;
};
export const getHistory = async () => {
    const response = await api.get('/history');
    return response.data;
};
export const submitFeedback = async (scanId, data) => {
    const response = await api.post(`/scan/${scanId}/feedback`, data);
    return response.data;
};
export default api;
