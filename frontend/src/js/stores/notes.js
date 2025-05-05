export default function () {
    return {
        _list: [],

        init() {
            this.fetch();
        },

        async fetch() {
            const { data } = await axiosInstance.get("/notes.php?action=view_notes");

            data.notes.forEach((note) => {
                delete note.user_id;

                try {
                    note.content = JSON.parse(note.content);
                } catch {
                    note.content = [];
                }
            });

            this._list = data.notes;
        },

        get(id) {
            return this._list.find(
                (note) => note.id === Number(id)
            );
        },

        async update(id, updateData) {
            try {
                const { data } = await axiosInstance.post("/update_note.php", {
                    note_id: parseInt(id),
                    ...updateData
                });

                console.log(data.message);

                await this.fetch();
            } catch (error) {
                console.error("Error logging in:", error);
                notyf.error("[500] Internal Server Error");
            }
        },

        async setTags(id, tags) {
            try {
                const formData = new FormData();

                formData.append("note_id", parseInt(id));
                tags.forEach((tag) => formData.append("tag_ids[]", tag));

                await axiosInstance.post("/notes.php?action=update_tags", formData);

                await this.fetch();
            } catch (error) {
                console.error("Error logging in:", error);
                notyf.error("[500] Internal Server Error");
            }
        },

        deltaToPreview(delta) {
            let preview = [];
        
            delta.forEach((op, i) => {
                if ("insert" in op) {
                    preview.push(op.insert);
                }
            });
        
            return preview.join("");
        }
    };
}