<?php
/**
 * Omeka
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Generate the form markup for entering element text metadata.
 * 
 * @package Omeka\View\Helper
 */
class Omeka_View_Helper_ElementForm extends Zend_View_Helper_Abstract
{
    /**
     * Displays a form for the record's element.
     * 
     * The function applies filters that allow plugins to customize the display of element form components.
     * Here is an example of how a plugin may add and implement an element form filter:
     *
     * add_filter(array('ElementForm', 'Item', 'Dublin Core', 'Title')), 'form_item_title');
     * function form_item_title(array $components, $args)
     * {
     *
     *   // Where $components would looks like:
     *   //  array(
     *   //      'label' => [...], 
     *   //      'inputs' => [...], 
     *   //      'description' => [...], 
     *   //      'comment' => [...], 
     *   //      'add_input' => [...], 
     *   //  )
     *   // and $args looks like:
     *   //  array(      
     *   //      'record' => [...],
     *   //      'element' => [...],
     *   //      'options' => [...],
     *   //  )
     * }
     *
     * @var Element
     */
    protected $_element;
    protected $_record;

    public function elementForm(Element $element, Omeka_Record_AbstractRecord $record, $options = array())
    {    
        $dcrdf["Title"] = 'title';
        $dcrdf["Alternative Title"] = 'alternative';
        $dcrdf["Subject"] = 'subject';
        $dcrdf["Description"] = 'description';
        $dcrdf["Abstract"] = 'abstract';
        $dcrdf["Table Of Contents"] = 'tableOfContents';
        $dcrdf["Creator"] = 'creator';
        $dcrdf["Source"] = 'source';
        $dcrdf["Publisher"] = 'publisher';
        $dcrdf["Date"] = 'date';
        $dcrdf["Date Modified"] = 'modified';
        $dcrdf["Date Available"] = 'available';
        $dcrdf["Date Created"] = 'created';
        $dcrdf["Date Accepted"] = 'dateAccepted';
        $dcrdf["Date Copyrighted"] = 'dateCopyrighted';
        $dcrdf["Date Submitted"] = 'dateSubmitted';
        $dcrdf["Date Issued"] = 'issued';
        $dcrdf["Date Valid"] = 'valid';
        $dcrdf["Contributor"] = 'contributor';
        $dcrdf["Rights"] = 'rights';
        $dcrdf["Access Rights"] = 'accessRights';
        $dcrdf["License"] = 'license';
        $dcrdf["Relation"] = 'relation';
        $dcrdf["Conforms To"] = 'conformsTo';
        $dcrdf["Has Format"] = 'hasFormat';
        $dcrdf["Has Part"] = 'hasPart';
        $dcrdf["Has Version"] = 'hasVersion';
        $dcrdf["Is Format Of"] = 'isFormatOf';
        $dcrdf["Is Part Of"] = 'isPartOf';
        $dcrdf["Is Referenced By"] = 'isReferencedBy';
        $dcrdf["Is Replaced By"] = 'isReplacedBy';
        $dcrdf["Is Required By"] = 'isRequiredBy';
        $dcrdf["Is Version Of"] = 'isVersionOf';
        $dcrdf["References"] = 'references';
        $dcrdf["Replaces"] = 'replaces';
        $dcrdf["Requires"] = 'requires';
        $dcrdf["Format"] = 'format';
        $dcrdf["Extent"] = 'extent';
        $dcrdf["Medium"] = 'medium';
        $dcrdf["Language"] = 'language';
        $dcrdf["Type"] = 'type';
        $dcrdf["Identifier"] = 'identifier';
        $dcrdf["Bibliographic Citation"] = 'bibliographicCitation';
        $dcrdf["Coverage"] = 'coverage';
        $dcrdf["Spatial Coverage"] = 'spatial';
        $dcrdf["Temporal Coverage"] = 'temporal';
        $dcrdf["Accrual Method"] = 'accrualMethod';
        $dcrdf["Accrual Periodicity"] = 'accrualPeriodicity';
        $dcrdf["Accrual Policy"] = 'accrualPolicy';
        $dcrdf["Audience"] = 'audience';
        $dcrdf["Audience Education Level"] = 'educationLevel';
        $dcrdf["Mediator"] = 'mediator';
        $dcrdf["Instructional Method"] = 'instructionalMethod';
        $dcrdf["Provenance"] = 'provenance';
        $dcrdf["Rights Holder"] = 'rightsHolder';

        $divWrap = isset($options['divWrap']) ? $options['divWrap'] : true;
        $extraFieldCount = isset($options['extraFieldCount']) ? $options['extraFieldCount'] : null;

        $this->_element = $element;

        // This will load all the Elements available for the record and fatal error
        // if $record does not use the ActsAsElementText mixin.
        $record->loadElementsAndTexts();
        $this->_record = $record;

        // Filter the components of the element form display
        $labelComponent = $this->_getLabelComponent();
        $inputsComponent = $this->_getInputsComponent($extraFieldCount);
        $descriptionComponent = $this->_getDescriptionComponent();
        $commentComponent = $this->_getCommentComponent();
        $addInputComponent = $this->view->formSubmit('add_element_' . $this->_element['id'], 
                                         __('Add Input'),
                                         array('class'=>'add-element'));
        $components = array(
            'label' => $labelComponent,
            'inputs' => $inputsComponent,
            'description' => $descriptionComponent,
            'comment' => $commentComponent,
            'add_input' => $addInputComponent,
            'html' => null 
        );

        $elementSetName = $element->set_name;
        $recordType = get_class($record);
        $filterName = array('ElementForm', $recordType, $elementSetName, $element->name);
        $components = apply_filters(
            $filterName, 
            $components,
            array('record' => $record, 
                  'element' => $element, 
                  'options' => $options)
        );

        if ($components['html'] !== null) {
            return strval($components['html']);
        }

        // Compose html for element form
        $html = $divWrap ? '<div class="field" id="element-' . html_escape($element->id) . '">' : '';
        
        $html .= '<div class="two columns alpha">';
        $html .= $components['label'];
        $dcrdfTerms  = array_keys($dcrdf);

        $html .= '<span style="color:#9d5b41; font-weight:bold; float:left; margin:-10px 0 10px;">dc-rdf:';
        $html .= in_array($this->_element['name'], $dcrdfTerms) ? $dcrdf[$this->_element['name']] : '';
        $html .= '</span>';

        $html .= $components['add_input'];
        $html .= '</div>'; // Close div

        $html .= '<div class="inputs five columns omega">';
        $html .= $components['description'];
        $html .= $components['comment'];
        $html .= $components['inputs'];
        $html .= '</div>'; // Close 'inputs' div

        $html .= $divWrap ? '</div>' : ''; // Close 'field' div


        return $html;
    }

    protected function _getFieldLabel()
    {
        return html_escape($this->_element['name']);
    }

    protected function _getFieldDescription()
    {
        return html_escape($this->_element['description']);
    }
    
    protected function _getFieldComment()
    {
        return html_escape($this->_element['comment']);
    }
    
    protected function _isPosted()
    {
        $postArray = $this->_getPostArray();
        return !empty($postArray);
    }

    protected function _getPostArray()
    {
        $elementId = $this->_element['id'];
        if (isset($_POST['Elements'][$elementId])) {
            return $_POST['Elements'][$elementId];
        } else {
            return array();
        }
    }

    /**
     * How many form inputs to display for a given element.
     *
     * @return integer
     */
    protected function _getFormFieldCount()
    {
        if ($this->_isPosted()) {
            $numFieldValues = count($this->_getPostArray());
        } else {
            $numFieldValues = count($this->getElementTexts());
        }

        // Should always show at least one field.
        return $numFieldValues ? $numFieldValues : 1;
    }

    /**
     * @uses ActsAsElementText::getTextStringFromFormPost()
     * @param integer
     * @return mixed
     */
    protected function _getPostValueForField($index)
    {
        if (!$this->_isPosted()) {
            // Return if there are no posted data.
            return null;
        }

        $postArray = $this->_getPostArray();
        if (!array_key_exists($index, $postArray)) {
            return '';
        }
        $postArray = $postArray[$index];

        // Flatten this POST array into a string so as to be passed to the
        // necessary helper functions.
        return $this->_record->getTextStringFromFormPost($postArray, $this->_element);
    }

    protected function _getHtmlFlagForField($index)
    {
        $isHtml = false;
        if ($this->_isPosted()) {
            $isHtml = (boolean) @$_POST['Elements'][$this->_element['id']][$index]['html'];
        } else {
            $elementText = $this->getElementTexts($index);
            if (isset($elementText)) {
                $isHtml = (boolean) $elementText->html;
            }
        }
        return $isHtml;
    }

    /**
     * Retrieve the form value for the field.
     *
     * @param integer
     * @return string
     */
    protected function _getValueForField($index)
    {
        if ($this->_isPosted()) {
            return $this->_getPostValueForField($index);
        } else {
            $elementText = $this->getElementTexts($index);
            if (isset($elementText)) {
                return $elementText->text;
            } else {
                return null;
            }
        }
    }

    /**
     * If index is not given, return all texts.
     *
     * @param string
     * @return void
     */
    public function getElementTexts($index=null)
    {
        $texts = $this->_record->getElementTextsByRecord($this->_element);
        if ($index !== null) {
            if (array_key_exists($index, $texts)) {
                return $texts[$index];
            } else {
                return null;
            }
        }
        return $texts;
    }

    protected function _getInputsComponent($extraFieldCount = null)
    {
        $fieldCount = $this->_getFormFieldCount() + (int) $extraFieldCount;
        $html = '';
        for ($i=0; $i < $fieldCount; $i++) {
            $html .= $this->view->elementInput(
                $this->_element, $this->_record, $i,
                $this->_getValueForField($i), $this->_getHtmlFlagForField($i));
        }
        return $html;
    }

    protected function _getDescriptionComponent()
    {
        return '<p class="explanation">' . __($this->_getFieldDescription()) .'</p>';
    }  
        
    protected function _getCommentComponent() 
    { 
        if ($this->_getFieldComment()) {
            return '<p class="explanation">' . $this->_getFieldComment() .'</p>';
        }
        return '';
    }

    protected function _getLabelComponent()
    {
        return '<label>' . __($this->_getFieldLabel()) . '</label>';
    }
}
