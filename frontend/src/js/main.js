import Alpine from "alpinejs";
import PineconeRouter from "pinecone-router";

import axios from "axios";

import { Notyf } from "notyf";
import "notyf/notyf.min.css";

const alpineData = import("./alpine/*.js");

window.API_URL = "http://localhost:8080";

window.axios = axios.create({
    baseURL: `${API_URL}/api`,
    withCredentials: true
})

window.notyf = new Notyf({
    duration: 5000,
    position: { y: "top" },
    dismissible: true,
    ripple: false
});

Alpine.plugin(PineconeRouter);

document.addEventListener("alpine:init", () => {
    window.PineconeRouter.settings({
        hash: true
    });

    Object.entries(alpineData).forEach(([name, module]) => {
        Alpine.data(name, module.default);
    });
});

Alpine.start();