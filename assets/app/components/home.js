
import Component from './component.js';
import Events from '../services/event.service.js';

const template = `<div>PAGE</div><ng-content></ng-content>`;

export default class Home extends Component {
    tagName = "app-home";
    html = template;

    constructor(events = Events) {
        super();

        this.events = events;
    }
}