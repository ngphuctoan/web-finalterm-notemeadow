import Alpine from "alpinejs";
import PineconeRouter from "pinecone-router";

Alpine.plugin(PineconeRouter);

document.addEventListener("alpine:init", () => {
    window.PineconeRouter.settings({
        hash: true
    });

    Alpine.data("app", () => ({
        darkMode: true
    }));
});

Alpine.start();