import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import PineconeRouter from "pinecone-router";

import _ from "lodash";

import axios, { formToJSON, isAxiosError } from "axios";

import { Notyf } from "notyf";
import "notyf/notyf.min.css";

import Quill from "quill";

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

window.Quill = Quill;

const notyf = new Notyf({
    duration: 5000,
    position: { y: "top" },
    dismissible: true,
    ripple: false
});

window.notyf = notyf;

Alpine.plugin(PineconeRouter);
Alpine.plugin(persist);

document.addEventListener("alpine:init", () => {
    window.PineconeRouter.settings({
        hash: true
    });

    Alpine.store("notes", notes());
    Alpine.store("tags", tags());

    Alpine.data("app", function () {
        return {
            profile: {},

            options: Alpine.$persist({
                listView: false
            }),
    
            init() {
                this.fetchProfile();
            },
    
            async fetchProfile() {
                const { data: profile } = await axiosInstance.get("/view_profile.php");
    
                delete profile["password"];
                delete profile["is_active"];
    
                this.profile = profile;
            },

            async uploadImage(file) {
                const formData = new FormData();
                formData.append("image", file);

                try {
                    const { data } = await axiosInstance.post("/upload_image.php",
                        formData,
                        { header: { "Content-Type": "multipart/form-data" } }
                    );

                    if (data.message.includes("Image uploaded successfully")) {
                        return data.file_path;
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error("Image upload failed");
                }
            }
        };
    });

    Alpine.store("syncStatus", "synced");

    Alpine.data("login", function () {
        return {
            loading: false,
    
            async login() {
                this.loading = true;
    
                try {
                    const { data } = await axiosInstance.post("/login.php",
                        formToJSON(new FormData(this.$el))
                    );
    
                    if (data.message.includes("Đăng nhập thành công")) {
                        this.$router.navigate("/");
                    } else {
                        notyf.error(data.message);
                    }
                } catch (error) {
                    console.error("Error logging in:", error);
                    notyf.error("[500] Internal Server Error");
                }
    
                this.loading = false;
            }
        };
    });

    Alpine.data("register", function () {
        return {
            loading: false,
    
            async register() {
                this.loading = true;
    
                try {
                    const { data } = await axiosInstance.post("/register.php",
                        formToJSON(new FormData(this.$el))
                    );
    
                    if (data.message.includes("Đăng ký thành công")) {
                        notyf.success(data.message);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (error) {
                    console.error("Error registering:", error);
                    notyf.error("[500] Internal Server Error");
                }
    
                this.loading = false;
            }
        };
    });

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