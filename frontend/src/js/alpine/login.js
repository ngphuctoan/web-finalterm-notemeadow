import axios, { formToJSON } from "axios";

export default function () {
    return {
        loading: false,

        async login() {
            this.loading = true;

            try {
                const { data } = await axios.post(
                    `${API_URL}/login.php`,
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
    }
}