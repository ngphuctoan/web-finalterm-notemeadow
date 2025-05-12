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

import "hint.css";

import "range-slider-element";
import "range-slider-element/dist/range-slider-element.css";

window.API_URL = process.env.API_URL || "http://localhost:8080";

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

    Alpine.store("theme", "auto");

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
    if (err?.isAxiosError && err.response) {
        const serverMsg = err.response.data?.message;
        if (serverMsg) {
            notyf.error(serverMsg);
            console.error("Axios error:", serverMsg);
        } else {
            notyf.error(msg);
            console.error("Axios error:", err);
        }
    } else if (err?.message) {
        notyf.error(err.message);
        console.error("Error:", err);
    } else {
        notyf.error(msg);
        console.error("Unknown error:", err);
    }
};

window.uploadImage = async (file) => {
    const formData = new FormData();
    formData.append("image", file);

    try {
        const { data } = await axiosInstance.post(
            "/upload_image.php", formData, {
                header: { "Content-Type": "multipart/form-data" }
            }
        );

        if (data.message === "Image uploaded successfully.") {
            return data.file_path;
        } else {
            notyf.error(data.message);
        }
    } catch (err) { handleServerError(err, "Cannot upload the image.") }
}

window.emailToHue = (email) => {
    let hash = 0;

    for (let i = 0; i < email.length; i++) {
      hash = email.charCodeAt(i) + ((hash << 5) - hash);
    }

    return Math.abs(hash) % 360;
}
 
window.placeholderAvatar = (display_name, email, size = 200) => {
    const hue = emailToHue(email);
    const initial = display_name.trim()[0].toUpperCase();
  
    const canvas = document.createElement("canvas");
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext("2d");
  
    ctx.fillStyle = `hsl(${hue}, 70%, 50%)`;
    ctx.fillRect(0, 0, size, size);
  
    ctx.fillStyle = "white";
    ctx.font = `bold ${size * .5}px "Plus Jakarta Sans"`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(initial, size / 2, size / 2 + 8);
  
    return canvas.toDataURL("image/png");
}