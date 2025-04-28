import axios from "axios";
import { Notyf } from "notyf";
import Alpine from "alpinejs";
import PineconeRouter from "pinecone-router";

import "notyf/notyf.min.css";

const notyf = new Notyf({
    duration: 5000,
    position: { y: "top" },
    dismissible: true,
    ripple: false
});

Alpine.plugin(PineconeRouter);

window.axios = axios;
window.notyf = notyf;

window.formDataToJSON = (formData) => {
    const jsonData = {};
    formData.forEach((value, key) => jsonData[key] = value);
    return jsonData;
}

document.addEventListener("alpine:init", () => {
    window.PineconeRouter.settings({
        hash: true
    });

    Alpine.data("app", () => ({
        darkMode: true
    }));
});

Alpine.start();