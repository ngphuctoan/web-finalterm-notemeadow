export default function () {
    return {
        list: [],

        init() {
            this.fetch();
        },

        async fetch() {
            try {
                const { data } = await axiosInstance.get(
                    "/notes.php?action=view_notes"
                );

                if (data.error) {
                    notyf.error(data.error);
                    return;
                }

                data.notes.forEach((note) => {
                    delete note.user_id;

                    try {
                        note.content = JSON.parse(note.content);
                    } catch {
                        note.content = [];
                    }
                });

                this.list = data.notes;
            } catch (err) { handleServerError(err, "Cannot fetch notes."); }
        },

        get(id) {
            return this.list.find(
                (note) => note.id === Number(id)
            );
        },

        async update(id, updateData) {
            try {
                const { data } = await axiosInstance.post(
                    "/update_note.php", { note_id: id, ...updateData }
                );

                if (!data.success) {
                    notyf.error(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        async setTags(id, tags) {
            try {
                const formData = new FormData();

                formData.append("note_id", parseInt(id));
                tags.forEach((tag) => formData.append("tag_ids[]", tag));

                const { data } = await axiosInstance.post(
                    "/notes.php?action=update_tags", formData
                );

                if (data.error) {
                    notyf.error(data.error);
                } else {
                    notyf.success("Tags have been set!");
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
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