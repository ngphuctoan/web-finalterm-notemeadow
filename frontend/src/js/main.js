import Alpine from "alpinejs";
import PineconeRouter from "pinecone-router";

import _ from "lodash";

import axios, { formToJSON } from "axios";

import { Notyf } from "notyf";
import "notyf/notyf.min.css";

import Quill from "quill";

import notes from "./stores/notes";
import tags from "./stores/tags";

window.API_URL = "http://localhost:8080";

window._ = _;

window.axiosInstance = axios.create({
    baseURL: `${API_URL}/api`,
    withCredentials: true
})

window.Quill = Quill;

const notyf = new Notyf({
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

    Alpine.store("notes", notes());
    Alpine.store("tags", tags());

    Alpine.data("app", function () {
        return {
            profile: {},

            options: {
                listView: false
            },
    
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
});

Alpine.start();