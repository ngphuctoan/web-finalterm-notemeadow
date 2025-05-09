import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import PineconeRouter from "pinecone-router";

import _ from "lodash";

import axios, { formToJSON } from "axios";

import { Notyf } from "notyf";
import "notyf/notyf.min.css";

import notes from "./stores/notes";
import tags from "./stores/tags";

import MicroModal from "micromodal";

import { computePosition, flip, offset, shift } from "@floating-ui/dom";

window.API_URL = "http://localhost:8080";

window._ = _;

window.axiosInstance = axios.create({
    baseURL: `${API_URL}/api`,
    withCredentials: true
});

window.formToJSON = formToJSON;

window.notyf = new Notyf({
    duration: 5000,
    dismissible: true,
    ripple: false
});

window.Alpine = Alpine;

Alpine.plugin(PineconeRouter);
Alpine.plugin(persist);

document.addEventListener("alpine:init", () => {
    window.PineconeRouter.settings({
        hash: true
    });

    Alpine.store("notes", notes());
    Alpine.store("tags", tags());

    Alpine.data("dropdown", function () {
        return {
            open: false,
            dropdownClass: ["hidden"],

            init() {
                this.$watch("open", async () => {
                    if (this.open) {
                        this.$refs.dropdownMenu.classList.remove(...this.dropdownClass);
                        await this.updatePos();
                    } else {
                        this.$refs.dropdownMenu.classList.add(...this.dropdownClass);
                    }
                });

                this.$refs.toggleBtn.onclick = (e) => {
                    e.stopPropagation();
                    this.toggle();
                }

                this.$refs.dropdownMenu.onclick = (e) =>
                    e.stopPropagation();

                this.$refs.dropdownMenu.classList.add(...this.dropdownClass);
            },

            toggle() {
                this.open = !this.open;
            },

            async updatePos() {
                const { x, y } = await computePosition(
                    this.$refs.toggleBtn, this.$refs.dropdownMenu, {
                        placement: "bottom-start",
                        middleware: [offset(8), flip(), shift()]
                    }
                );

                this.$refs.dropdownMenu.style.top = `${y}px`;
                this.$refs.dropdownMenu.style.left = `${x}px`;
            }
        }
    });
});

Alpine.start();

document.addEventListener("pinecone:end", () => {
    MicroModal.init({ disableScroll: true });
})

window.withLoading = async (setLoading, fn) => {
    setLoading(true);
    await fn();
    setLoading(false);
}

window.handleServerError = (err, msg = "Something went wrong! Please try again later.") => {
    notyf.error(msg);
    console.error(err.stack || err);
}

window.uploadImage = async (file) => {
    const formData = new FormData();
    formData.append("image", file);

    try {
        const { data } = await axiosInstance.post(
            "/upload_image.php", formData, {
                header: { "Content-Type": "multipart/form-data" }
            }
        );

        if (data.message.includes("Image uploaded successfully")) {
            return data.file_path;
        }
        throw new Error(data.message);
    } catch (err) { handleServerError(err, "Cannot upload the image.") }
}