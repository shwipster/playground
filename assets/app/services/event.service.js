

class Event {

    events = {};

    constructor() { }

    subscribe(thisArg, eventName, callback) {
        if (!(eventName in this.events)) {
            this.events[eventName] = new Map();
        }

        this.events[eventName].set(thisArg, {
            callback: callback.bind(thisArg),
            more: thisArg.tagName
        });
    }

    publish(eventName, payload) {

        if (eventName in this.events) {
            this.events[eventName].forEach((element) => {
                console.log(element);
                element.callback(payload);
            });
        }
    }

    unsubscribe(thisArg, eventName) {
        if (eventName in this.events) {
            this.events[eventName].delete(thisArg);
        }
    }
}

let EventService = new Event();

export default EventService;
export let EventServiceType = Event;