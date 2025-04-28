import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";

export default {
    theme: {
        extend: {
            colors: {
                primary: colors.yellow["600"]
            }
        }
    },
    plugins: [forms]
};