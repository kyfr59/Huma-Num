<?php

class OaipmhHarvester_Form_Harvest extends Omeka_Form
{
    public function init()
    {
        $options = unserialize(get_option('nakala_import_settings'));
        $url = $options['nakala-oai-url'];

        parent::init();
        $this->addElement('hidden', 'base_url', array(
            'label' => 'URL OAI',
            'value' => $url,
            'description' => 'Par exemple :<br />https://www.nakala.fr/oai/11280/aae1de78?verb=ListRecords&metadataPrefix=oai_dc
',        'size' => 80,
        ));
        
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
        
        echo "oo";
        
        $this->addElement('submit', 'submit_view_sets', array(
            'label' => 'Voir votre structuration NAKALA',
            'class' => 'submit submit-medium',
            'decorators' => (array(
                'ViewHelper', 
                array('HtmlTag', array('tag' => 'div', 'class' => 'field'))))
        ));
    }
}
