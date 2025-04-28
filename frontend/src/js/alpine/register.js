import axios, { formToJSON } from "axios";

export default function () {
    return {
        loading: false,

        async register() {
            this.loading = true;

            try {
                const { data } = await axios.post(
                    `${API_URL}/register.php`,
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
    }
}