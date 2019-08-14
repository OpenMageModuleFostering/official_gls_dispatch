<?php
# ToDo add license text here

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_Shipment
{

	/**
	 * @var	string	$customerId	The required customer Id
	 * 							This is a request payload entity
	 */
	private $customerId;

	/**
	 * @var	string	$contactId	The required contact Id
	 * 							This is a request payload entity
	 */
	private $contactId;

    /**
     * @var	string	$shipperId	The required contact Id
     * 							This is a request payload entity
     */
    private $shipperId;

	/**
	 * @var	string	$shipmentDate	The required date of the shipment using format "YYYYMMDD" with maxlenth of 8 chars
	 * 								This is a request payload entity
	 */
	private $shipmentDate;

    /**
     * @var	array	$references	The required references
     * 							This is a request payload entity
     */
    private $references;

	/**
	 * @var	string	$sandbox	The optional information, if the connector should use the sandbox or not
	 * 							This is a request payload entity
     *                          Note, that the GLS-API name for this property is "system"
	 */
	private $sandbox;

	/**
	 * @var	string	$reference	A optional customer given reverence with max length of 50 chars
	 * 							This is a request payload entity
	 */
	private $reference;

	/**
	 * @var array	$addresses	Container node(associated array) for all SynergeticAgency_GlsConnector_Model_Address addresses
	 * 							This is a request payload entity
	 */
	private $addresses = array();

	/**
	 * @var array	$parcels	Container node(numeric array) for all SynergeticAgency_GlsConnector_Model_Parcel parcels
	 * 							All included parcels must have the same service configuration
	 * 							At least one parcel is required
	 */
	private $parcels = array();


    /**
     * @var	string	$labelSize	The optional format of the label.
     * 							Allowed values are: "A4", "A5", "A6"(default)
     * 							This is a request payload entity
     */
    private $labelSize = "A6";


	/**
	 * @var	szring	$consignmentId	The shipment number
	 * 								This is a response payload entity
	 */
	private $consignmentId;

    /**
     * @var	string	$pdf	The required base64-encoded PDF including labels of all created returns.
     * 						The labels of each parcel are on separate pages
     * 						Labels created on the sandbox system hava a “DEMO” mark on it
     * 						This is a request payload entity
     */
    #private $pdf;


    /**
     * @var	array	$labels	The required base64-encoded PDF's including labels of all created returns.
     * 						The labels of each parcel are on separate pages
     * 						Labels created on the sandbox system hava a “DEMO” mark on it
     * 						This is a request payload entity
     */
    private $labels;


	/**
	 * @var	array	$labelContentLength	The content length of $this->labels[n]
	 */
	private $labelContentLength;

	/**
	 * @var SynergeticAgency_GlsConnector_Model_Services	$services	Container for all SynergeticAgency_GlsConnector_Model_Services services
	 * 							The availability of services is dependent on SynergeticAgency_GlsConnector_Connector::username
	 */
	private $services;


    /**
     * @var	bool sets with return label or without
     */
    private $returnLabel = false;

	/**
	 *
	 */
	function __construct() {
        //echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":";

		$address = new SynergeticAgency_GlsConnector_Model_Address();
		$address->setType( SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_DELIVERY );
		$this->addAddress( $address );

        //echo "\n\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":";
	}


	/**
	 * converts the the GLS Web API relevant fields of this shipment model into an ECMA-404 conform JSON format
	 * see also "JSON Data Interchange Format" on http://www.ecma-international.org/publications/files/ECMA-ST/ECMA-404.pdf
	 *
	 * @return string
	 */
	public function toJson(){
		$returnValue = Array();

		// mapping global fields
		#$returnValue['customerid'] = $this->getCustomerId();
        #$returnValue['contactid'] = $this->getContactId();
        $returnValue['shipperId'] = $this->getShipperId();
        $returnValue['shipmentDate'] = $this->getShipmentDate();
        $returnValue['labelSize'] = $this->getLabelSize();

        // GLS-API uses parameter "System=TEST" to define that all requests pointing to the GLS-Sandbox
        // The Magento-GLS-Extension uses true|false to determine Sandbox-Mode(true) or not(false), so we have to map true to "TEST"
        // On the other hand, send no parameter "System" if the productive GLS-System should be used(Sandbox-Mode(false)).
        #if( $this->getSandbox() === true ){
        #    $returnValue['System'] = 'TEST';
        #}

		$returnValue['references'] = $this->getReferences();

		// mapping address fields
		foreach( $this->getAddresses() as $type => $address ){
			/** @var $address SynergeticAgency_GlsConnector_Model_Address */
			$returnValue['addresses'][$type] = $address->getGlsArray();
		}

		// mapping parcel fields
		foreach( $this->getParcels() as $parcelNum => $parcel ){
			/** @var $parcel SynergeticAgency_GlsConnector_Model_Parcel */
			$returnValue['parcels'][$parcelNum] = $parcel->getGlsArray();
            if($this->getReturnLabel()) {
                $returnValue['returns'][$parcelNum]['weight'] = $parcel->getWeight();
            }

            if(count($parcel->getServices())) {
                foreach ($parcel->getServices() as $serviceNum => $service) {
                    /** @var $parcel SynergeticAgency_GlsConnector_Model_Service */
                    $returnValue['parcels'][$parcelNum]['services'][$serviceNum] = $service->getGlsArray();

                    if (count($service->getInfos())) {
                        foreach ($service->getInfos() as $infoNum => $info) {
                            /** @var $parcel SynergeticAgency_GlsConnector_Model_Info */
                            $returnValue['parcels'][$parcelNum]['services'][$serviceNum]['infos'][$infoNum] = $info->getGlsArray();
                        }
                    }
                }
            }
        }

		return json_encode( $returnValue );
	}


	/**
	 * @return string
	 */
	public function getCustomerId() {
		return $this->customerId;
	}

	/**
	 * @param $customerId
	 * @return $this
	 */
	public function setCustomerId($customerId) {
		$this->customerId = $customerId;
        #$this->setShipperId( $this->getCustomerId() . " " . $this->getContactId() );
		return $this;
	}

	/**
 * @return string
 */
    public function getContactId() {
        return $this->contactId;
    }

    /**
     * @param $contactId
     * @return $this
     */
    public function setContactId($contactId) {
        $this->contactId = $contactId;
        #$this->setShipperId( $this->getCustomerId() . " " . $this->getContactId() );
        return $this;
    }

    /**
     * @return string
     */
    public function getShipperId() {
        return $this->shipperId;
    }

    /**
     * @param $shipperId
     * @return $this
     */
    public function setShipperId($shipperId) {
        $this->shipperId = $shipperId;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getShipmentDate() {
		return $this->shipmentDate;
	}

	/**
	 * @param $shipmentDate
	 * @return $this
	 */
	public function setShipmentDate($shipmentDate) {
		$this->shipmentDate = $shipmentDate;
		return $this;
	}

    /**
     * @return string
     */
    public function getReferences() {
        return $this->references;
    }

    /**
     * @param array $references
     * @return $this
     */
    public function setReferences( $references ) {

        if( is_array($references) ){
            $this->references = $references;
        }
        return $this;
    }

    /**
     * @param $reference
     * @param null $referenceKey
     * @return $this
     */
    public function pushReference( $reference, $referenceKey = NULL ) {

        if( $referenceKey !== NULL && $referenceKey !== '' ){
            $this->references[$referenceKey] = $reference;
        } else {
            $this->references[] = $reference;
        }

        return $this;
    }

	/**
	 * @return string
	 */
	public function getSandbox() {
		return $this->sandbox;
	}

    /**
     * @param $sandbox
     * @return $this
     */
	public function setSandbox($sandbox) {
		$this->sandbox = (Boolean)$sandbox;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getReference() {
		return $this->reference;
	}

	/**
	 * @param $reference
	 * @return $this
	 */
	public function setReference($reference) {
		$this->reference = $reference;
		return $this;
	}

	/**
	 * @param mixed $type
	 * @return array|SynergeticAgency_GlsConnector_Model_Address
	 */
	public function getAddresses( $type = false ) {
		$returnValue = $this->addresses;

		if( $type !== false && isset($this->addresses[$type]) ){
			$returnValue = $this->addresses[$type];
		} else if( is_array($type) ){
			$returnValue = array();
			foreach( $type as $k => $n ){
				if( isset($this->addresses[$n]) && is_a($this->addresses[$n], SynergeticAgency_GlsConnector_Model_Address )){
					$returnValue[$n] = $this->addresses[$n];
				}
			}
			#if( count($returnValue) === 0 ){
			#	$returnValue = false;
			#}
		}
		return $returnValue;
	}

	/**
	 * @param $addresses
	 * @return $this
	 */
	public function setAddresses($addresses) {
		$this->addresses = $addresses;
		return $this;
	}

	/**
	 * @param SynergeticAgency_GlsConnector_Model_Address $address
	 * @return $this
	 */
	public function addAddress( SynergeticAgency_GlsConnector_Model_Address $address) {

		if( in_array ( $address->getType() , $address->getConstants() ) ){
			$this->addresses[$address->getType()] = $address;
		}

		return $this;
	}


	/**
	 * @param mixed $num
	 * @return array
	 */
	public function getParcels( $num = false ) {
		$returnValue = $this->parcels;
		if( $num !== false && is_numeric($num) && isset($this->parcels[$num]) ){
			$returnValue = $this->parcels[$num];
		} else if( is_array($num) ){
			$returnValue = array();
			foreach( $num as $k => $n ){
				if( isset($this->parcels[$n]) && is_a($this->parcels[$n], SynergeticAgency_GlsConnector_Model_Parcel )){
					$returnValue[$n] = $this->parcels[$n];
				}
			}
			#if( count($returnValue) === 0 ){
			#	$returnValue = $this->parcels;
			#}
		}

		return $returnValue;
	}

	/**
	 * @param $parcels
	 * @return $this
	 */
	public function setParcels($parcels) {
		$this->parcels = $parcels;
		return $this;
	}

	/**
	 * @param SynergeticAgency_GlsConnector_Model_Parcel $parcel
	 * @return $this
	 */
	public function setParcel( SynergeticAgency_GlsConnector_Model_Parcel $parcel) {

		if( isset( $this->parcels[$parcel->getNum()] ) ){
			$this->parcels[$parcel->getNum()] = $parcel;
		}

		return $this;
	}

	/**
	 * @param SynergeticAgency_GlsConnector_Model_Parcel $parcel
	 * @return $this
	 */
	public function pushParcel( SynergeticAgency_GlsConnector_Model_Parcel $parcel ){
		array_push( $this->parcels, $parcel );
		return $this;
	}

    /**
     * @return string
     */
    public function getLabelSize() {
        return $this->labelSize;
    }

    /**
     * @param $labelSize
     * @return $this
     */
    public function setLabelSize($labelSize) {
        $this->labelSize = $labelSize;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getConsignmentId() {
		return $this->consignmentId;
	}

	/**
	 * @param $consignmentId
	 * @return $this
	 */
	public function setConsignmentId($consignmentId) {
		$this->consignmentId = $consignmentId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPdf() {
		return $this->pdf;
	}

	/**
	 * set the base64-encoded PDF and $this->pdfContentLength
	 * @param	string	$pdf	base64-encoded PDF
	 * @return	$this
	 */
	public function setPdf($pdf) {
		$this->pdf = $pdf;
		$this->setPdfContentLength( strlen($pdf) );
		return $this;
	}

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param $label
     * @return $this
     */
    public function pushLabel( $label ){
        $this->pushLabelContentLength( strlen($label) );
        $this->labels[] = $label;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getLabelContentLength() {
		return $this->labelContentLength;
	}

	/**
	 * @param $labelContentLength
	 * @return $this
	 */
	public function setLabelContentLength($labelContentLength) {
		$this->labelContentLength = $labelContentLength;
		return $this;
	}

    /**
     * @param $label
     * @return $this
     */
    public function pushLabelContentLength( $label ){
        $this->labelContentLength[] = $label;
        return $this;
    }

	/**
	 * @return array
	 */
	public function getServices() {
		return $this->services;
	}

	/**
	 * @param $services
	 * @return $this
	 */
	public function setServices($services) {
		$this->services = $services;
		return $this;
	}

    /**
     * @return bool
     */
    public function getReturnLabel() {
        return $this->returnLabel;
    }

    /**
     * @param bool $returnlabel
     * @return $this
     */
    public function setReturnLabel($returnlabel) {
        $this->returnLabel = (bool)$returnlabel;
        return $this;
    }
}