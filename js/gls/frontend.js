var GlsParcelShop = Class.create();
GlsParcelShop.prototype = {
    initialize: function (options) {

        var parcelShop = this;

        parcelShop.options = Object.extend({
            config:'',
            countryConfig:'',
            origin: window.location.href,
            searchButton:$('shipping:findGlsParcelShop'),
            addressSelect:$('shipping-address-select'),
            shippingFormButton:$('shipping-buttons-container').select('button')[0],
            shippingFormFields:$('shipping-new-address-form'),
            billingSwitch:$('billing:use_for_parcelshop'),
            shippingSwitch:$('shipping:parcelshop_active'),
            sameAsBillingSwitch:$('shipping:same_as_billing'),
            shopInfoContainerId:'gls-parcel-shop-information',
            notSelectedLabel: 'Please select a parcel shop',
            invalidShopLabel: 'The selected shop is not allowed for your country',
            billingInfoUrl:'',
            parcelShopFinderUrl:'',
            address : {
                name:{field:$('shipping:parcelshop_name'),hidden:true},
                id:{field:$('shipping:parcelshop_id'),hidden:true},
                street:{field:$('shipping:street1')},
                street2:{field:$('shipping:street2')},
                zip:{field:$('shipping:postcode')},
                city:{field:$('shipping:city')},
                region: {field:$('shipping:region')},
                regionid: {field:$('shipping:region_id')},
                country: {field:$('shipping:country_id')}
            },
            isActive: false,
            triggerElm:'',
            popup: {
                height:'800',
                width:'1000',
                resizable:'1'
            },
            //document callbacks for community developers!!!
            onInitBefore: function(){
                //manipulate foreign DOM elements
                parcelShop.onInitDefault();
            },
            onInitAfter: function(){
            },
            onActiveBefore: function(){
                //manipulate foreign DOM elements
                parcelShop.onActiveBeforeDefault();
            },
            onActiveAfter: function(){
            },
            onInactiveBefore: function(){
                //manipulate foreign DOM elements
                parcelShop.onInactiveBeforeDefault();
            },
            onInactiveAfter: function(){
            },
            setParcelShopAddressAfter:  function(){
                //manipulate DOM elements
                parcelShop.showParcelShopAddress();
            },
            mapParcelShopData:function(){
                parcelShop.mapParcelShopDataDefault();
            },
            mapFormFields:function(){
                parcelShop.setDefaultFormFieldValues();
            }
        },options);

        parcelShop.isActive = parcelShop.options.isActive;
        parcelShop.data = [];
        parcelShop.history = ['addressSelect', 'billingSwitch', 'sameAsBillingSwitch', 'shippingFormButton'];

        //assigning initial settings of control switches to history
        if (parcelShop.options.addressSelect) {
            parcelShop.history.addressSelect = parcelShop.options.addressSelect.getValue();
        }
        if (parcelShop.options.billingSwitch) {
            parcelShop.history.billingSwitch = parcelShop.getRadioValue('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"]');
        }
        if (parcelShop.options.sameAsBillingSwitch) {
            parcelShop.history.sameAsBillingSwitch = parcelShop.options.sameAsBillingSwitch.checked;
        }

        /** call onInitBefore callback **/
        parcelShop.options.onInitBefore();

        /** get country config into object **/
        parcelShop.config = parcelShop.strToJson(parcelShop.options.config);

        /** get foreign country dependency config **/
        parcelShop.countryConfig = parcelShop.strToJson(parcelShop.options.countryConfig);

        /** assign parcel shop relevant input field to mapping object **/
        parcelShop.options.mapFormFields();

        /** set status active if isActive option is true **/
        if(parcelShop.isActive == true) {
            parcelShop.setStatus();
        }
        else{
            $(parcelShop.options.searchButton).hide();
        }

        /** observe control elements **/

        /** observe billing "ship to parcel shop" radio button **/
        if (parcelShop.options.billingSwitch) {
            parcelShop.options.billingSwitch.observe('focus', function () {
                parcelShop.history.billingSwitch = parcelShop.getRadioValue('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"]');
            }.bind(this));
            $$('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"]').invoke('on','change',function(){
                    parcelShop.triggerElm = parcelShop.options.billingSwitch;
                    parcelShop.setStatus();
                }
            );
        }

        /** observe shipping form "ship to parcel shop" checkbox **/
        parcelShop.options.shippingSwitch.observe('change', function () {
            parcelShop.triggerElm = parcelShop.options.shippingSwitch;
            parcelShop.setStatus();
        }.bind(this));

        /** observe click event on element opening the parcel shop map **/
        if (parcelShop.options.searchButton) {
            parcelShop.options.searchButton.observe('click', function () {
                new Ajax.Request(parcelShop.options.billingInfoUrl, {
                    method : 'get',
                    onSuccess: function(response) {
                        var mapParams = parcelShop.strToJson(response.responseText);
                        var countryCode = mapParams.country_id;
                        var zip = mapParams.postcode;
                        parcelShop.openProxy(countryCode,zip);
                    },
                    onFailure: function() { parcelShop.openProxy(); }
                });
            }.bind(this));
        }

        /** observe "same as billing" checkbox in shipping address form  **/
        if(parcelShop.options.sameAsBillingSwitch) {
            parcelShop.options.sameAsBillingSwitch.observe('change', function () {
                parcelShop.options.shippingSwitch.checked = false;
                parcelShop.triggerElm = parcelShop.options.sameAsBillingSwitch;
                parcelShop.history.sameAsBillingSwitch = parcelShop.options.sameAsBillingSwitch.checked;
                parcelShop.setStatus();
            }.bind(this));
        }

        /** call onInitAfter callback **/
        parcelShop.options.onInitAfter();
    },

    /**
     * figure out if parcel shop is active or not and take the according actions
     * @return void
     */
    setStatus: function() {
        var parcelShop = this;

        if (parcelShop.options.shippingSwitch && parcelShop.options.billingSwitch) {
            if ((parcelShop.options.shippingSwitch.checked == true || parcelShop.options.billingSwitch.checked == true) && parcelShop.isActive == false) {
                parcelShop.setStatusActive();
            }
            if ((parcelShop.options.shippingSwitch.checked == false || parcelShop.options.billingSwitch.checked == false) && parcelShop.isActive == true) {
                parcelShop.setStatusInactive();
            }
        }
    },

    /**
     * do all initial operations if parcel shop form is set to active
     * @return void
     */
    setStatusActive: function () {
        var parcelShop = this;

        parcelShop.isActive = true;
        parcelShop.options.onActiveBefore();
        parcelShop.setControlElementsStatus();
        parcelShop.setAddressFieldStatus();
        parcelShop.options.onActiveAfter();
    },

    /**
     * do all initial operations if parcel shop form is set to inactive
     * @return void
     */
    setStatusInactive: function () {
        var parcelShop = this;

        parcelShop.isActive = false;
        parcelShop.options.onInactiveBefore();
        parcelShop.setControlElementsStatus();
        if(parcelShop.triggerElm == parcelShop.options.sameAsBillingSwitch){
            parcelShop.showAddressFields();
        }else {
            parcelShop.setAddressFieldStatus();
        }

        parcelShop.options.onInactiveAfter();
    },

    /**
     * set all checkout controls to correct state in dependency if parcel shop is active or not
     * @return void
     */
    setControlElementsStatus: function () {
        var parcelShop = this;

        if(parcelShop.isActive == true) {

            if(!parcelShop.options.address.id.value){

                parcelShop.history.shippingFormButton = parcelShop.options.shippingFormButton.readAttribute('onclick');
                parcelShop.options.shippingFormButton.removeAttribute('onclick');
            }

            if(parcelShop.options.sameAsBillingSwitch) {
                parcelShop.options.sameAsBillingSwitch.checked = false;
            }

            if(parcelShop.getRadioValue('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"]') != parcelShop.options.billingSwitch.getValue()) {
                parcelShop.history.billingSwitch = parcelShop.getRadioValue('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"]');
            }

            /** activate elements which can toggle active state **/
            parcelShop.options.shippingSwitch.checked = true;
            parcelShop.options.billingSwitch.checked = true;

            /** show button to open the parcel shop finder pop up **/
            parcelShop.options.searchButton.show();

            /** manage the shipping address select field **/
                //remember old value
            if(parcelShop.options.addressSelect) {
                parcelShop.history.addressSelect = parcelShop.options.addressSelect.getValue();
                //empty value which should be "new address"
                parcelShop.options.addressSelect.setValue('');
                // set field to readonly
                parcelShop.options.addressSelect.disabled = true;
                parcelShop.options.addressSelect.addClassName('readonly');
                //trigger magento default change actions
                parcelShop.dispatchChangeEvent($(parcelShop.options.addressSelect));
            }
            //show the form - just for IE - should happen on change of addressSelect
            parcelShop.options.shippingFormFields.show();
        }
        else {
            if(parcelShop.history.shippingFormButton){
                parcelShop.options.shippingFormButton.setAttribute('onclick',parcelShop.history.shippingFormButton);
            }
            /** unselect elements which can toggle active state **/
            if(parcelShop.options.shippingSwitch) {
                parcelShop.options.shippingSwitch.checked = false;
            }
            if(parcelShop.triggerElm != parcelShop.options.billingSwitch && $$('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"][value="' + parcelShop.history.billingSwitch + '"]').length) {
                $$('*[name="' + parcelShop.options.billingSwitch.readAttribute('name') + '"][value="' + parcelShop.history.billingSwitch + '"]')[0].checked = true;
            }
            /** hide button to open the parcel shop finder pop up **/
            if(parcelShop.options.searchButton) {
                parcelShop.options.searchButton.hide();
            }

            /** set the sameAsBillingSwitch to same value it had before parcel shop was activated **/
            if(parcelShop.options.sameAsBillingSwitch) {
                if(parcelShop.triggerElm != parcelShop.options.sameAsBillingSwitch) {
                    parcelShop.options.sameAsBillingSwitch.checked = parcelShop.history.sameAsBillingSwitch;
                }
            }

            /** manage the shipping address select field **/
            if(parcelShop.options.addressSelect) {

                    // set field to !readonly
                    parcelShop.options.addressSelect.disabled = false;
                    parcelShop.options.addressSelect.removeClassName('readonly');
                if (parcelShop.triggerElm != parcelShop.options.sameAsBillingSwitch) {
                    //set value to value before activation
                    parcelShop.options.addressSelect.setValue(parcelShop.history.addressSelect);
                    //trigger magento default change actions but not if same as billing is set
                    if (parcelShop.triggerElm != parcelShop.options.sameAsBillingSwitch) {
                        parcelShop.dispatchChangeEvent(parcelShop.options.addressSelect);
                    }
                }
            }
        }
    },

    /**
     * open parcel shop map pop up and listen to message event to take data from popup
     * the parcel shop map is embedded with an iframe due to IE compatibility issues
     * @return void
     */
    openProxy: function(countryCode,zip) {
        var parcelShop = this;

        if(typeof countryCode == 'undefined'){
            if (parcelShop.options.address.country.field.value) {
                countryCode = parcelShop.options.address.country.field.value;
            }
            else {
                countryCode = parcelShop.config.id;
            }
        }

        if(parcelShop.options.address.zip.field.value && typeof zip == 'undefined'){
            zip = parcelShop.options.address.zip.field.value;
        }

        if(countryCode != '' && typeof countryCode != 'undefined'){
            countryCode = '&countryCode=' + countryCode;
        }
        if(zip != '' && typeof zip != 'undefined'){
            zip = '&zip=' + zip;
        }

        //open parcel shop finder map
        var url = parcelShop.options.parcelShopFinderUrl + '?origin=' + parcelShop.options.origin + countryCode + zip;

        window.open(url, 'glsParcelShopProxy', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=' + parcelShop.options.popup.resizable +', width=' + parcelShop.options.popup.width +', height=' + parcelShop.options.popup.height);
        window.addEventListener('message', function(e) {
            if(typeof e.data!="undefined" && e.data[0].eventType == 'shopSelected') {
                parcelShop.data = parcelShop.toJson(e.data);
                parcelShop.options.mapParcelShopData();

                if(parcelShop.isParcelShopDeliveryAllowed(parcelShop.data[0].eventData.address.country)){
                    parcelShop.setParcelShopAddress();
                    if(parcelShop.history.shippingFormButton){
                        parcelShop.options.shippingFormButton.setAttribute('onclick',parcelShop.history.shippingFormButton);
                    }
                    parcelShop.options.setParcelShopAddressAfter();
                }
                else{
                    parcelShop.options.shippingFormButton.removeAttribute('onclick');
                    parcelShop.options.shippingFormFields.insert(
                        {'top':  $(parcelShop.options.shopInfoContainerId).update('<dt class="error">' + parcelShop.options.invalidShopLabel + '</dt>')}
                    );
                    $(parcelShop.options.shopInfoContainerId).update('<dt class="error">' + parcelShop.options.invalidShopLabel + '</dt>');
                }
            }
        }, false);
    },

    /**
     * check if parcel shop delivery is allowed for a country
     * @param currentParcelShopCountry
     * @returns {boolean}
     */
    isParcelShopDeliveryAllowed: function(currentParcelShopCountry) {
        var parcelShop = this;
        var allowedParcelShopCountries = [];
        var isAllowed = false;

        if(parcelShop.countryConfig.domestic.parcelshop == true){
            allowedParcelShopCountries.push(parcelShop.config.id);
        }

        for (var key in parcelShop.countryConfig.foreign.countries) {
            if(parcelShop.countryConfig.foreign.countries[key].parcelshop == true){
                allowedParcelShopCountries.push(key);
            }
        }

        if(allowedParcelShopCountries.indexOf(currentParcelShopCountry) != -1){
            isAllowed = true;
        }

        return isAllowed;
    },

    /**
     * some operations on foreign DOM elements called in the onInit callback
     * @return Void
     */
    onInitDefault: function() {
        var parcelShop = this;

        /** prepend parcelshop information to shipping form **/
        parcelShop.options.shippingFormFields.insert(
            {'top': new Element('dl', { 'id': parcelShop.options.shopInfoContainerId}).update('<dt class="error">' + parcelShop.options.notSelectedLabel + '</dt>')}
        );

        /** just show information if parcel shop active, hide on init **/
        $('gls-parcel-shop-information').hide();

        /** move billingSwitch to nicer position **/
        if($$('#co-billing-form > .fieldset > .form-list').length && parcelShop.options.billingSwitch) {
            $$('#co-billing-form > .fieldset > .form-list')[0].appendChild(parcelShop.options.billingSwitch.up());
        }

        /** move parcel shop fields to nicer position **/
        if(parcelShop.options.shippingFormFields && $('parcelshop_fields') &&  $('co-shipping-form')) {
            $('parcelshop_fields').childElements().each(function(elm){
                $('co-shipping-form').appendChild(elm);
            });
        }

        /** move shippingSwitch to nice position **/
        if(parcelShop.options.shippingFormFields && parcelShop.options.shippingSwitch) {
            parcelShop.options.shippingFormFields.insert(
                {'before': parcelShop.options.shippingSwitch.up()}
            );
        }
    },
    /**
     * some operations on foreign DOM elements called in the onActiveBefore callback
     * @return Void
     */
    onActiveBeforeDefault: function() {

        $('shipping:street1').disabled == true;
        $('shipping:street1').removeClassName('required-entry');

        if($('shipping:save_in_address_book')) {
            $('shipping:save_in_address_book').up().hide();
        }
        if($('gls-parcel-shop-information')) {
            $('gls-parcel-shop-information').show();
        }
    },

    /**
     * some operations on foreign DOM elements called in the onInactiveBeforeDefault callback
     * @return Void
     */
    onInactiveBeforeDefault: function() {
        $('shipping:street1').disabled == false;

        if($('shipping:save_in_address_book')) {
            $('shipping:save_in_address_book').up().show();
        }
        $('gls-parcel-shop-information').hide();
    },

    /**
     * parse json...
     * @return {Object}
     */
    toJson: function(obj) {
        return JSON.parse(JSON.stringify(obj, null, 2));
    },

    /**
     * parse json...
     * @return {Object}
     */
    strToJson: function(obj) {
        return JSON.parse(obj);
    },

    /**
     * set status of relevant fields in dependency if parcel form is active or not
     * @param {String} status
     * @return void
     */
    setAddressFieldStatus: function () {
        var parcelShop = this;

        for (var key in parcelShop.options.address) {
            if (parcelShop.options.address.hasOwnProperty(key)) {
                if(parcelShop.options.address[key].field) {
                    if(parcelShop.isActive == true) {

                        parcelShop.options.address[key].newAddressFormValue = parcelShop.options.address[key].field.getValue();
                        parcelShop.options.address[key].field.setValue(parcelShop.options.address[key].parcelShopFormValue);
                        parcelShop.options.address[key].field.readOnly = true;
                        if(!parcelShop.options.address[key].hidden) {
                            parcelShop.options.address[key].field.up().up().hide();
                        }
                        parcelShop.options.address[key].field.addClassName('readonly');
                        parcelShop.dispatchChangeEvent(parcelShop.options.address[key].field);
                    }
                    else {
                        parcelShop.options.address[key].parcelShopFormValue =  parcelShop.options.address[key].field.getValue();
                        parcelShop.options.address[key].field.value = parcelShop.options.address[key].newAddressFormValue;
                        parcelShop.options.address[key].field.readOnly = false;
                        if(!parcelShop.options.address[key].hidden) {
                            parcelShop.options.address[key].field.up().up().show();
                        }
                        parcelShop.options.address[key].field.removeClassName('readonly');
                        parcelShop.dispatchChangeEvent(parcelShop.options.address[key].field);
                    }
                }
            }
        }
    },
    /**
     * just show the field hidden by parcel search again - let magento do everything else
     * @param {String} status
     * @return void
     */
    showAddressFields: function () {
        var parcelShop = this;
        for (var key in parcelShop.options.address) {
            if (parcelShop.options.address.hasOwnProperty(key)) {
                if(parcelShop.options.address[key].field) {
                    parcelShop.options.address[key].parcelShopFormValue =  parcelShop.options.address[key].field.getValue();
                    parcelShop.options.address[key].field.readOnly = false;
                    if(!parcelShop.options.address[key].hidden) {
                        parcelShop.options.address[key].field.up().up().show();
                    }
                    parcelShop.options.address[key].field.removeClassName('readonly');
                }
            }
        }
    },

    /**
     * show parcel shop address in a box
     * @return void
     */
    showParcelShopAddress: function() {
        var parcelShop = this;

        $('gls-parcel-shop-information').childElements().each(function(el){
            $(el).remove();
        });

        parcelShop.options.shippingFormFields.appendChild($(parcelShop.options.shopInfoContainerId));
        for (var key in parcelShop.options.address) {
            if (parcelShop.options.address.hasOwnProperty(key)) {
                if(parcelShop.options.address[key].field) {
                    switch(key) {
                        case 'id':
                        case 'region':
                        case 'regionid':
                        case 'street2':
                            break;
                        case 'country':
                            $('gls-parcel-shop-information').appendChild(new Element('dt', { 'class': 'gls-label '+ key}).update(parcelShop.getLabel(parcelShop.options.address[key].field.up().previous()) + ':'));
                            $('gls-parcel-shop-information').appendChild(new Element('dd', { 'class': 'gls-value '+ key}).update(parcelShop.options.address[key].field[parcelShop.options.address[key].field.selectedIndex].text));
                            break;
                        default:
                            $('gls-parcel-shop-information').appendChild(new Element('dt', { 'class': 'gls-label '+ key}).update(parcelShop.getLabel(parcelShop.options.address[key].field.up().previous()) + ':'));
                            $('gls-parcel-shop-information').appendChild(new Element('dd', { 'class': 'gls-value '+ key}).update(parcelShop.options.address[key].field.value));
                    }
                }
            }
        }
    },

    /**
     * set value of relevant address fields to address values taken from parcel shop finder
     * @return void
     */
    setParcelShopAddress: function() {
        var parcelShop = this;
        for (var key in parcelShop.options.address) {
            if (parcelShop.options.address.hasOwnProperty(key)) {
                if(parcelShop.options.address[key].field) {
                    switch(key) {
                        default:
                            parcelShop.options.address[key].field.value = parcelShop.options.address[key].value;
                            parcelShop.dispatchChangeEvent(parcelShop.options.address[key].field);
                    }
                }
            }
        }
    },

    /**
     * map data from parcel shop map pop up to mapping object
     * can't be done dynamic as there there can be exceptions -> street
     * also names don't match...
     * values are used in setParcelShopAddress()
     * @return void
     */
    mapParcelShopDataDefault: function() {
        var parcelShop = this;
        parcelShop.data.map(function(item) {
            //shop name
            parcelShop.options.address.name.value     = item.eventData.name;
            //shop id
            parcelShop.options.address.id.value       = item.eventData.id;
            //shop street
            parcelShop.options.address.street.value   = item.eventData.address.street + ' ' + item.eventData.address.streetNo;
            //shop postal code
            parcelShop.options.address.zip.value      = item.eventData.address.zipCode;
            //shop place
            parcelShop.options.address.city.value     = item.eventData.address.city;
            //shop country
            parcelShop.options.address.country.value  = item.eventData.address.country;
        });
    },

    /**
     * setting the initial values of parcel shop address mapping object
     * @return void
     */
    setDefaultFormFieldValues: function() {
        var parcelShop = this;

        for (var key in parcelShop.options.address) {
            if (parcelShop.options.address.hasOwnProperty(key)) {
                if(parcelShop.options.address[key].field) {
                    parcelShop.options.address[key].value = '';
                    parcelShop.options.address[key].newAddressFormValue = parcelShop.options.address[key].field.getValue();
                    parcelShop.options.address[key].parcelShopFormValue = '';
                }
            }
        }
    },

    /**
     * trigger change event. mostly needed to keep magento defaults js work
     * @return Void
     */
    dispatchChangeEvent: function ($elm) {
        var event = document.createEvent('HTMLEvents');
        event.initEvent('change', true, false);
        $elm.dispatchEvent(event);
    },

    /**
     * get currently checked radio button of radio group
     * @return String
     */
    getRadioValue: function ($elm) {
        return $$($elm + ':checked').first().value;
    },

    /**
     * ie8 compatible way to get just the label
     * @return String
     */
    getLabel: function ($elm) {

        if($elm.childElements().length > 0) {
            $elm.firstDescendant().remove();
        }
        return $elm.innerHTML;
    },


    serializeForms: function () {
        var parcelShop = this;

        parcelShop.formsData = [];
        parcelShop.options.forms.each(function(item) {
            if($$(item)[0].tagName.toLowerCase() == 'form') {
                parcelShop.formsData[item] = decodeURIComponent($$(item)[0].serialize());
                Object.keys(parcelShop.formsData).each(function(formSelector) {
                    var params = parcelShop.formsData[formSelector].toQueryParams();
                    Object.keys(params).each(function(key) {

                        if(key != $(parcelShop.options.shippingSwitch).readAttribute('name') && key != $(parcelShop.options.billingSwitch).readAttribute('name')) {
                            Form.Element.setValue($$(formSelector + ' *[name="' + key + '"]')[0], params[key]);
                        }
                    });
                });
            }
        });
    },
    restoreForms: function () {
        var parcelShop = this;
        Object.keys(parcelShop.formsData).each(function(formSelector) {
            var params = parcelShop.formsData[formSelector].toQueryParams();
            Object.keys(params).each(function(key) {

                if(key != $(parcelShop.options.shippingSwitch).readAttribute('name') && key != $(parcelShop.options.billingSwitch).readAttribute('name')) {

                    Form.Element.setValue($$(formSelector + ' *[name="' + key + '"]')[0], params[key]);
                }
            });
        });
    }
}