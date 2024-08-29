import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';
import 'datatables.net-bs5';
import axios from 'axios';

export default class extends Controller {
    static targets = ['currentButton'];

    connect() {
        if (!$.fn.DataTable.isDataTable(this.element)) {
            $(this.element).DataTable();
        }
    }

}
