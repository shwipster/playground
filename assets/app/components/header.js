import Footer from './footer.js';
import Component from './component.js';
import Events from '../services/event.service.js';


const template = `
    <div>HEADER</div>
    <ng-content></ng-content>
`;

export default class Header extends Component {

    tagName = "app-header";
    html = template;

    constructor(events = Events) {
        super();

        this.events = events;
        this.events.subscribe(this, "test", this.test);
    }

    test() {
        console.log(this.tagName);
    }

    render() {
        super.render();

        //setTimeout(this.render.bind(this), 2000);
    }
}