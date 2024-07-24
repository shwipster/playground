import Mustache from 'mustache';
import Handlebars from "handlebars";

class Component extends HTMLElement {
    tagName = null;
    html = null;
    template = null;

    data = {}

    static observedAttributes = [];

    constructor() {
        super();
    }

    async render() {

        // 1 Create virtual dom and replace template variables
        var tpl = document.createElement('template');
        tpl.innerHTML = this.template(this, { allowProtoMethodsByDefault: true });
        let virtualDom = tpl.content;

        // 2 Move child nodes into this component dom
        let content = virtualDom.querySelector("ng-content");
        if (content && this.childNodesList) {
            content.replaceWith(...this.childNodesList);
        }

        // 3 Append virtual dom to browser dom
        this.replaceChildren(virtualDom);
    }

    connectedCallback() {
        //console.log("Custom element added to page. " + this.tagName);

        // 1 Compile JS code from HTML string
        this.template = Handlebars.compile(this.html);

        //Remeber nodes that are childer
        this.childNodesList = [];
        for (let node of this.childNodes) {
            this.childNodesList.push(node);
        }

        this.classList.add("component");
        this.render();
    }

    disconnectedCallback() {
        console.log("Custom element removed from page." + this.tagName);
    }

    adoptedCallback() {
        console.log("Custom element moved to new page." + this.tagName);
    }

    attributeChangedCallback(name, oldValue, newValue) {
        console.log(`Attribute ${name} has changed.`);
    }
}

export default Component;