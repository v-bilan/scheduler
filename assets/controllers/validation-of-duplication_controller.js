import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['witness'];

    connect() {
     //   console.log('connected!!!');
    }

    validateOnSubmit(event){
        var unique = new Set()
        var duplicated  = [];
        this.witnessTargets.forEach((element) => {
            if (unique.has(element.value)) {
                duplicated.push(element.options[element.selectedIndex].innerHTML);
            }
            unique.add(element.value)
        })
        if (duplicated.length) {
            if (! confirm(' Наступні вісники мають завдання декілька раз ' + duplicated.toString() + '. Продовжити?')) {
                event.preventDefault();
            };
        }

    }
}