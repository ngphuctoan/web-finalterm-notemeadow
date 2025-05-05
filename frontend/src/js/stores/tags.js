export default function () {
    return {
        _list: [],
        actives: [],

        init() {
            this.fetch();
        },

        async fetch() {
            const { data: tags } = await axiosInstance.get("/tags.php?action=list_tags");
            
            tags.forEach((tag) => delete tag.user_id);
            
            this._list = tags;
        },

        get(id) {
            return this._list.find(
                (note) => note.id === Number(id)
            );
        },

        async add(name) {
            try {
                const { data } = await axiosInstance.post("/tags.php?action=add_tag", {
                    name
                });

                console.log(data.message);

                await this.fetch();
            } catch (error) {
                console.error("Error logging in:", error);
                notyf.error("[500] Internal Server Error");
            }
        },

        async rename(id, new_name) {
            try {
                const { data } = await axiosInstance.put("/tags.php?action=rename_tag", {
                    tag_id: id, old_name: this.get(id).name, new_name
                });

                if (data.error) {
                    throw new Error(data.error);
                }

                console.log(data.message);

                await this.fetch();
            } catch (error) {
                console.error("Error logging in:", error);
                notyf.error("[500] Internal Server Error");
            }
        },

        async delete(id) {
            try {
                const { data } = await axiosInstance.delete("/tags.php?action=delete_tag", {
                    data: { tag_id: id }
                });

                console.log(data.message);

                await this.fetch();
            } catch (error) {
                console.error("Error logging in:", error);
                notyf.error("[500] Internal Server Error");
            }
        }
    };
}