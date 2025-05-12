export default function () {
    return {
        list: [],
        sharedList: [],

        colors: {
            gray: "bg-gray-300 dark:bg-gray-900",
            red: "bg-rose-300 dark:bg-rose-950",
            yellow: "bg-amber-200 dark:bg-yellow-900",
            green: "bg-green-300 dark:bg-green-900",
            blue: "bg-sky-300 dark:bg-sky-900",
            purple: "bg-purple-300 dark:bg-purple-950"
        },

        searchQuery: "",

        async fetch() {
            try {
                const { data } = await axiosInstance.get(
                    "/notes.php?action=view_notes"
                );

                if (data.error) {
                    notyf.error(data.error);
                    return;
                }

                const sharedByMe = await this.getSharedByMe();

                data.notes.forEach((note) => {
                    delete note.user_id;

                    try {
                        note.content = JSON.parse(note.content);
                    } catch {
                        note.content = [];
                    }

                    note.created_at = new Date(note.created_at);
                    note.modified_at = new Date(note.modified_at);

                    note.shared = sharedByMe[note.id];
                    note.shared_id = null;
                });

                const sharedWithMe = await this.getSharedWithMe();

                sharedWithMe.forEach((note) => {
                    try {
                        note.content = JSON.parse(note.content);
                    } catch {
                        note.content = [];
                    }
                    
                    note.created_at = new Date(note.created_at);
                    // Copy created_at field for modified_at field to make sorting work
                    note.modified_at = new Date(note.created_at);

                    note.shared = null;
                });

                this.list = [...data.notes, ...sharedWithMe];
            } catch (err) { handleServerError(err, "Cannot fetch notes."); }
        },

        async getSharedByMe() {
            const { data } = await axiosInstance.get(
                "share_note.php?action=shared_by_me"
            );

            return data.reduce((note, { id, shared_id, ...others }) => {
                if (!note[id]) note[id] = [];
                note[id].push({ shared_id, ...others });
                return note;
            }, {});
        },

        async getSharedWithMe() {
            return (await axiosInstance.get(
                "share_note.php?action=shared_with_me"
            )).data;
        },

        get(id) {
            return this.list.find(
                (note) => note.id === Number(id)
            );
        },

        matchesQuery(note) {
            const q = this.searchQuery.toLowerCase();
            const matchesTitle = note.title?.toLowerCase().includes(q);

            if (note.shared_id) return !q || matchesTitle;
            if (note.password) return !q;

            const noteContent = this.deltaToPreview(note.content).toLowerCase();
            
            return matchesTitle || noteContent.includes(q);
        },

        matchesTagFilters(note, filterTags) {
            if (note.shared_id) {
                return filterTags.length === 0;
            }

            return filterTags.every((tagId) =>
                note.tags.map((tag) => tag.id).includes(Number(tagId)) &&
                !note.password
            );
        },

        async create(formData) {
            try {
                const { data } = await axiosInstance.post(
                    "/notes.php?action=create_note", formData
                );

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

                await this.fetch();

                if (!data.success) {
                    notyf.error(data.message);
                    return false;
                } else {
                    return true;
                }
            } catch (err) {
                handleServerError(err);
                return false;
            }
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

        async share(id, recipients) {
            try {
                const { data } = await axiosInstance.put(
                    "/share_note.php", { note_id: id, recipients }
                );

                for (const { email, message } of data) {
                    if (message === "Access permission has been updated.") {
                        notyf.success(`[${email}] ${message}`);
                    } else {
                        notyf.error(`[${email}] ${message}`);
                    }
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        async changeSettings(id, user_id, note_color, font_size) {
            try {
                const { data } = await axiosInstance.patch(
                    "/note_settings.php", { id, user_id, note_color, font_size }
                );

                if (data.message.includes("đã")) {
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