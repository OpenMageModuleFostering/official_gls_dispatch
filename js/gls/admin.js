var PrototypeExtensions = {
    data: function (elem, key, val) {
        var DATA_REGEX = /data-([\w-]+)/;
        var ii = 0;
        var nattr = elem.attributes.length;
        if (key && val) {
            elem.setAttribute('data-' + key, val);
        } else {
            for (; ii < nattr; ++ii) {
                var attr = elem.attributes[ii];
                if (attr && attr.name) {
                    var m = attr.name.match(DATA_REGEX);
                    if (m && m.length > 1) {
                        var datakey = m[1];
                        if (datakey === key) {
                            return attr.value;
                        }
                    }
                }
            }
        }
        return null;
    }
};

Element.addMethods(PrototypeExtensions);

if (typeof tmpVarienGridMassaction == 'undefied') {
    var tmpVarienGridMassaction = {};
}
tmpVarienGridMassaction = Class.create(varienGridMassaction, {
    onGridRowClick: function (grid, evt) {
        var tdElement = Event.findElement(evt, 'td');
        var trElement = Event.findElement(evt, 'tr');

        if($(tdElement).down('.qty-parcels-wrap')) {
            return;
        }
        if (!$(tdElement).down('input')) {
            if ($(tdElement).down('a') || $(tdElement).down('select')) {
                return;
            }
            if (trElement.title) {
                setLocation(trElement.title);
            }
            else {
                var checkbox = Element.select(trElement, 'input');
                var isInput  = Event.element(evt).tagName == 'input';
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;

                if (checked) {
                    this.checkedString = varienStringArray.add(checkbox[0].value, this.checkedString);
                } else {
                    this.checkedString = varienStringArray.remove(checkbox[0].value, this.checkedString);
                }
                this.grid.setCheckboxChecked(checkbox[0], checked);
                this.updateCount();
            }
            return;
        }

        if (Event.element(evt).isMassactionCheckbox) {
            this.setCheckbox(Event.element(evt));
        } else if (checkbox = this.findCheckbox(evt)) {
            checkbox.checked = !checkbox.checked;
            this.setCheckbox(checkbox);
        }
    }
});
varienGridMassaction = tmpVarienGridMassaction;