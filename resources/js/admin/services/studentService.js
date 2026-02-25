import axios from "axios";

export default {
    getStudents(params) {
        return axios.get("/admin/students", { params });
    },

    createStudent(data) {
        return axios.post("/admin/students", data, {
            headers: { "Content-Type": "multipart/form-data" },
        });
    },

    updateStudent(id, data) {
        return axios.post(`/admin/students/${id}?_method=PUT`, data, {
            headers: { "Content-Type": "multipart/form-data" },
        });
    },

    deleteStudent(id) {
        return axios.delete(`/admin/students/${id}`);
    },
};
