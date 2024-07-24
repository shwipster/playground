
import Component from './component.js';
import Events from '../services/event.service.js';

const template = `ABOUT`;

export default class About extends Component {
    tagName = "app-about";
    html = template;

    constructor(events = Events) {
        super();

        this.events = events;
    }
}