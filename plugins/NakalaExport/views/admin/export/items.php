<nkl:Data xmlns:nkl="http://nakala.fr/schema#"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xmlns:dcterms="http://purl.org/dc/terms/"
           xsi:schemaLocation="http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2008/02/11/dcterms.xsd">
            <?php 
            $requiredElements = array("Title", "Creator", "Type", "Created");
            foreach($requiredElements as $elementName) {
                if (isset($this->elements[$elementName][0]) && $elementValue = $this->elements[$elementName][0]) {
                    echo "\n\t<dcterms:".strtolower($elementName).">". $elementValue ."</dcterms:".strtolower($elementName).">";
                    unset($this->elements[$elementName][0]);                    
                } else {
                    echo "\n\t<dcterms:".strtolower($elementName)."></dcterms:".strtolower($elementName).">"; 
                }

            }

            $dublinCoreElements = array("Contributor", "Coverage", "Creator", "Date", "Description", "Format", "Identifier",  "Language", "Publisher", "Relation", "Rights", "Source", "Subject", "Title", "Type");
            foreach($this->elements as $elementName => $element)
                {
                    foreach($element as $e)
                        if(in_array($elementName, $dublinCoreElements))
                            echo "\n\t<dcterms:".strtolower($elementName).">$e</dcterms:".strtolower($elementName).">";
                }
            ?>  
        <dcterms:source>OMEKA</dcterms:source>     
            <?php if ($this->nakala_collection): ?>
                <nkl:inCollection><?php echo $this->nakala_collection ?></nkl:inCollection>
            <?php endif; ?>
<?php echo "\n"; ?></nkl:Data>
