import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";

export default {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: colors.yellow["600"]
            }
        }
    },
    plugins: [forms]
};