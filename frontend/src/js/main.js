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

window.DEFAULT_PROFILE_IMAGE = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCI+PHBhdGggZmlsbD0iI2ZjYzIxYiIgZD0iTTY0IDkuNDdDMS40OCA5LjQ3IDAgNzkuNTUgMCA5My40MmMwIDEzLjg4IDI4LjY1IDI1LjExIDY0IDI1LjExczYzLjk5LTExLjIzIDYzLjk5LTI1LjExYzAtMTMuODctMS40Ny04My45NS02My45OS04My45NSIvPjxwYXRoIGZpbGw9IiMyZjJmMmYiIGQ9Ik02NC4xNCAxNDQuNmMtMTcuNDQgMC0yNS4wNS0xMS4xLTI1LjM4LTExLjU0YTMuMTcgMy4xNyAwIDAgMSAuNjItNC40M2EzLjE1MyAzLjE1MyAwIDAgMSA0LjQxLjYxYy4zLjM4IDYuMTkgOS4wNCAyMC41NiA5LjA0YzEzLjI0IDAgMTkuNDYtOC4zNSAxOS43Mi04LjczYy45OS0xLjQzIDIuOTUtMS44IDQuMzktLjgyYTMuMTYgMy4xNiAwIDAgMSAuODUgNC4zN2MtLjMzLjUtOC4wMyAxMS41LTI1LjE3IDExLjUiLz48cGF0aCBmaWxsPSIjMmYyZjJmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMTUuNjkgNTAuNjhjLS4wMy0uNzgtLjExLTQuMDctLjI0LTQuODVjLS4zLTEuNzYtLjQ4LTEuODQtMi4yNS0yLjE1Yy0yOC40NS01LjAyLTMzLjUyIDEuMDYtNDkuMiAxLjM5Yy0xNS42OC0uMzItMjAuNzYtNi40MS00OS4yLTEuMzljLTEuNzcuMzItMS45NS4zOS0yLjI1IDIuMTVjLS4xMi43OC0uMjEgNC4wOC0uMjQgNC44NWMtLjA2IDEuNDQuMjMgMS42MyAxLjY2IDIuNGMxLjI1LjY2IDEuODMuODYgMS44MyAzLjQ5YzAgMTcuMzUgNy44OCAyMi43MSAxOC40NiAyMy4xM2MxMS4xOS40NCAyMC4yOS01LjkzIDI0LjA4LTE1LjY5YzIuNjEtNi43MyA0LjE1LTEwLjE2IDUuNjctMTAuMzNjMS41MS4xNyAzLjA1IDMuNjEgNS42NyAxMC4zM2MzLjc5IDkuNzcgMTIuODkgMTYuMTMgMjQuMDggMTUuNjljMTAuNTktLjQyIDE4LjQ2LTUuNzggMTguNDYtMjMuMTNjMC0yLjYzLjU4LTIuODMgMS44My0zLjQ5YzEuNDEtLjc3IDEuNy0uOTYgMS42NC0yLjRtLTYwLjY2IDUuMDVDNTIuMTkgNjQgNDguNzggNzQuODEgMzQuNCA3NC4yN2MtMTAuNjMtLjM5LTEzLjcyLTcuOTgtMTMuOTQtMTguMDVjLS4xNS02LjUzLjc4LTguMDQgNC45Mi04Ljg5YzMuODgtLjggNy44Ni0xLjE4IDExLjc4LTEuMDNjNC43Mi4xOSAxMC4xMiAxLjIxIDEzLjc0IDIuODJjNC42MyAyLjA4IDUuMTQgMy42OSA0LjEzIDYuNjFtNTIuNS41Yy0uMjIgMTAuMDYtMy4zMSAxNy42Ni0xMy45NCAxOC4wNWMtMTQuMzcuNTMtMTcuNzgtMTAuMjgtMjAuNjItMTguNTVjLTEuMDEtMi45Mi0uNS00LjU0IDQuMTMtNi42MWMzLjYyLTEuNjEgOS4wMi0yLjY0IDEzLjc0LTIuODJjMy45Mi0uMTUgNy45LjIzIDExLjc4IDEuMDNjNC4xNC44NSA1LjA2IDIuMzcgNC45MSA4LjkiIGNsaXAtcnVsZT0iZXZlbm9kZCIvPjxwYXRoIGZpbGw9IiNlZDZjMzAiIGQ9Ik05MS43NiA4My45NmMtMy45Ni00LjAzLTEyLjQgMS4wNy0yNy43NiAxLjA3cy0yMy44LTUuMS0yNy43Ni0xLjA3Yy0uOTggMS0xLjM4IDMuMzUtLjM1IDQuOTNjMi44MiA0LjMyIDEzLjMxIDExLjQ1IDI4LjEgMTEuN2MxNC43OS0uMjQgMjUuMjktNy4zOCAyOC4xLTExLjdjMS4wNS0xLjU4LjY1LTMuOTMtLjMzLTQuOTMiLz48ZGVmcz48cGF0aCBpZD0ibm90b1YxTmVyZEZhY2UwIiBkPSJNOTEuNzYgODMuOTZjLTMuOTYtNC4wMy0xMi40IDEuMDctMjcuNzYgMS4wN3MtMjMuOC01LjEtMjcuNzYtMS4wN2MtLjk4IDEtMS4zOCAzLjM1LS4zNSA0LjkzYzIuODIgNC4zMiAxMy4zMSAxMS40NSAyOC4xIDExLjdjMTQuNzktLjI0IDI1LjI5LTcuMzggMjguMS0xMS43YzEuMDUtMS41OC42NS0zLjkzLS4zMy00LjkzIi8+PC9kZWZzPjxjbGlwUGF0aCBpZD0ibm90b1YxTmVyZEZhY2UxIj48dXNlIGhyZWY9IiNub3RvVjFOZXJkRmFjZTAiLz48L2NsaXBQYXRoPjxnIGZpbGw9IiNmZmYiIGNsaXAtcGF0aD0idXJsKCNub3RvVjFOZXJkRmFjZTEpIj48cGF0aCBkPSJNNTIuOTMgODMuOTF2NS41YTMuNzQgMy43NCAwIDAgMCAzLjIyIDMuNzFsMy41OC41MWMyLjI1LjMyIDQuMjctMS40MyA0LjI3LTMuNzF2LTYuMDFzLTYuOTQuNjYtMTEuMDcgMG0yMi4xNCAwdjUuNWEzLjc0IDMuNzQgMCAwIDEtMy4yMiAzLjcxbC0zLjU4LjUxQTMuNzQ3IDMuNzQ3IDAgMCAxIDY0IDg5Ljkydi02LjAxczYuOTQuNjYgMTEuMDcgMCIvPjwvZz48cGF0aCBmaWxsPSIjMmYyZjJmIiBkPSJNNDIgNjhjLTQuNDkuMDQtOC4xNy00LjI3LTguMjItOS42MmMtLjA1LTUuMzcgMy41NS05Ljc1IDguMDQtOS43OWM0LjQ4LS4wNCA4LjE3IDQuMjcgOC4yMiA5LjY0Yy4wNSA1LjM2LTMuNTUgOS43My04LjA0IDkuNzdtNDQuMTEgMGM0LjQ4LS4wMSA4LjExLTQuMzYgOC4xLTkuNzFjLS4wMS01LjM3LTMuNjYtOS43LTguMTQtOS42OWMtNC40OS4wMS04LjEzIDQuMzYtOC4xMiA5LjczYy4wMiA1LjM1IDMuNjcgOS42OCA4LjE2IDkuNjciLz48L3N2Zz4=";

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