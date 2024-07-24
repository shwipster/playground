
import Component from './component.js';
import Events from '../services/event.service.js';

const template = `
    <div>FOOTER</div>
    <ng-content></ng-content>
`;

export default class Footer extends Component {
    tagName = "app-footer";
    html = template;

    constructor(events = Events) {
        super();

        this.events = events;
        this.events.subscribe(this, "test", this.test);
    }

    test() {
        console.log(this.tagName);
    }
}