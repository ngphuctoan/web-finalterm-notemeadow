export default function () {
    return {
        list: [],
        actives: [],

        init() {
            this.fetch();
        },

        async fetch() {
            const { data: tags } = await axiosInstance.get(
                "/tags.php?action=list_tags"
            );
            
            tags.forEach((tag) => delete tag.user_id);
            
            this.list = tags;
        },

        get(id) {
            return this.list.find(
                (note) => note.id === Number(id)
            );
        },

        async add(name) {
            try {
                const { data } = await axiosInstance.post(
                    "/tags.php?action=add_tag", { name }
                );

                if (data.message.includes("thêm")) {
                    notyf.success(data.message);
                } else {
                    notyf.error(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        async rename(id, new_name) {
            try {
                const { data } = await axiosInstance.put(
                    "/tags.php?action=rename_tag", {
                        tag_id: id,
                        old_name: this.get(id).name,
                        new_name
                    }
                );

                if (data.message.includes("thành công")) {
                    notyf.success(data.message);
                } else {
                    notyf.error(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        },

        async delete(id) {
            try {
                const { data } = await axiosInstance.delete(
                    "/tags.php?action=delete_tag", {
                        data: { tag_id: id }
                    }
                );

                if (data.message.includes("xóa")) {
                    notyf.success(data.message);
                } else {
                    notyf.error(data.message);
                }

                await this.fetch();
            } catch (err) { handleServerError(err); }
        }
    };
}