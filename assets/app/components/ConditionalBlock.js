/**
 *  Show hide blocks based on input value
 *  Specify the name of input to listen to and on what values the block should be visible
 *
 * <form>
 *
 *     <input type="radio" name="category" value="M1">
 *     <input type="radio" name="category" value="N1">
 *     <input type="radio" name="category" value="L">
 *
 *     <conditional-group listen="category" values="M1,N1">
 *         <div>showme</div>
 *     </conditional-group>
 * </form>
 *
 */

class ConditionalBlock extends HTMLElement
{
    static observedAttributes = [];

    inputToListen = null;
    valuesToListen = null;
    hideClass = "d-none";
    origClasses = "";

    constructor()
    {
        super();


    }

    //Built-in method. Called when attached to DOM
    connectedCallback()
    {
        if ( !("listen" in this.attributes)) {
            return;
        }

        if (!("values" in  this.attributes)) {
            return;
        }

        this.origClasses = this.className;
        this.inputToListen = this.attributes.listen.value;
        let values = this.attributes.values.value;
        this.valuesToListen = values.split(',');

        if ("hideClass" in this.attributes) {
            this.hideClass = this.attributes.hideClass.value;
        }

        //Bind change event to input
        let parentForm = this.closest("form");
        if (!parentForm) {
            parentForm = document;
        }

        let selector = "input[name='" + this.inputToListen + "']";
        let inputToListen = parentForm.querySelectorAll(selector);

        if (!inputToListen) {
            return;
        }

        let initialChecked = null;
        inputToListen.forEach( (node) => {

            if (node.checked) {
                initialChecked = node.value;
            }

            node.addEventListener('change', (e) => {

                //Decide if current block is visible based on input value
                let inputValue = node.value;
                this.toggleVisibility(inputValue);
            });
        });

        //Check if fields initially activated. Otherwise hide by default
        this.toggleVisibility(initialChecked);
    }

    toggleVisibility(inputValue)
    {


        if (this.valuesToListen.includes(inputValue)) {
            this.classList.remove(this.hideClass);
            this.className = this.origClasses;

            //Select all inputs that are not descendants of 'conditional-block.d-none'
            //Selects inputs that are visible to user
            let inputs = this.querySelectorAll('input:not(conditional-block.d-none *)');  //All inputs under current element
            inputs.forEach( (inp) => {inp.setAttribute("required", '')});
        } else {

            this.className = "";
            this.classList.add(this.hideClass);

            let inputs = this.querySelectorAll("input");  //All inputs under current element
            inputs.forEach( (inp) => {inp.removeAttribute("required")});
        }
    }
}

customElements.define("conditional-block", ConditionalBlock, {});