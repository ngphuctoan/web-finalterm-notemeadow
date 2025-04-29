export default function () {
    return {
        _list: [],

        init() {
            this.fetch();
        },

        async fetch() {
            const { data: notes } = await axiosInstance.get("/get_notes.php");

            notes.forEach((note) => {
                delete note.user_id;

                try {
                    note.content = JSON.parse(note.content);
                } catch {
                    note.content = [];
                }
            });

            this._list = notes;
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