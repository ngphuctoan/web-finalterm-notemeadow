export default function () {
    return {
        profile: {},

        notes: [],
        tags: [],

        options: {
            listView: false
        },

        init() {
            this.fetchProfile();
            this.fetchNotes();
            this.fetchTags();
        },

        async fetchNotes() {
            const { data: notes } = await axios("/get_notes.php");

            notes.forEach((note) => delete note["user_id"]);

            this.notes = notes;
        },

        async fetchTags() {
            const { data: tags } = await axios("/tags.php?action=list_tags");

            tags.forEach((tag) => delete tag["user_id"]);

            this.tags = tags;
        },

        async fetchProfile() {
            const { data: profile } = await axios("/view_profile.php");

            delete profile["password"];
            delete profile["is_active"];

            this.profile = profile;
        },

        getPinnedNotes() {
            return this.notes.filter((note) => note.pinned);
        },

        getUnpinnedNotes() {
            return this.notes.filter((note) => !note.pinned);
        },

        getProfilePicture() {
            return `${API_URL}/api/${this.profile.image}`;
        }
    }
}