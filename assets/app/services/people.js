

class PeopleService {
    get() {
        return [
            { name: "Yehuda Katz" },
            { name: "Alan Johnson" },
            { name: "Charles Jolley" }
        ]
    }
}

export default new PeopleService()