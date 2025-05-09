
export default function () {
    return {
        list: [],

        colors: {
            gray: "bg-gray-300 dark:bg-gray-900",
            red: "bg-rose-300 dark:bg-rose-950",
            yellow: "bg-amber-200 dark:bg-yellow-900",
            green: "bg-green-300 dark:bg-green-900",
            blue: "bg-sky-300 dark:bg-sky-900",
            purple: "bg-purple-300 dark:bg-purple-950"
        },

        searchQuery: "",

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

                    note.created_at = new Date(note.created_at);
                    note.modified_at = new Date(note.modified_at);
                });

                this.list = data.notes;
            } catch (err) { handleServerError(err, "Cannot fetch notes."); }
        },

        get(id) {
            return this.list.find(
                (note) => note.id === Number(id)
            );
        },

        matchesQuery(id) {
            const q = this.searchQuery.toLowerCase();
            const note = this.get(id);
            const noteContent = this.deltaToPreview(note.content).toLowerCase();

            // Ignores password-protected notes
            if (q && note.password) {
                return false;
            }

            return note.title?.toLowerCase().includes(q) || noteContent.includes(q);
        },

        async create(formData) {
            try {
                const { data } = await axiosInstance.post(
                    "/notes.php?action=create_note", formData
                );wdqwdwdqwqdwqdwqdwqdqwd

                if (!data.id) {
                    notyf.error(data.message);
                }

                await this.fetch();

                return data.id;
            } catch (err) { handleServerError(err); }
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

        async updatePass(id, passData) {
            try {
                const { data } = await axiosInstance.put(
                    "/notes.php?action=change_password", {
                        note_id: id,
                        ...formToJSON(passData),
                        current_password: this.get(id)?.password || ""
                    }
                );

                if (!data.message.includes("thay đổi")) {
                    notyf.error(data.message);
                } else {
                    notyf.success(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        async delete(id) {
            try {
                const { data } = await axiosInstance.delete(
                    "/delete_note.php", {
                        data: { note_id: id }
                    }
                );

                if (data.success) {
                    notyf.success(data.message);
                } else {
                    notyf.error(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        deltaToPreview(delta) {
            let preview = [];
        
            delta.forEach((op, i) => {
                if ("insert" in op && typeof op.insert === "string") {
                    preview.push(op.insert);
                }
            });
        
            return preview.join("");
        }
    };
}