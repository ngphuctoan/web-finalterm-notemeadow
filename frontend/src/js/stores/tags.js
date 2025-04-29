export default function () {
    return {
        _list: [],

        init() {
            this.fetch();
        },

        async fetch() {
            const { data: tags } = await axiosInstance.get("/tags.php?action=list_tags");
            this._list = tags;
        }
    };
}