<nkl:Data xmlns:nkl="http://nakala.fr/schema#"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xmlns:dcterms="http://purl.org/dc/terms/"
           xsi:schemaLocation="http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2008/02/11/dcterms.xsd">
    <?php 

        $dcElementNames = array( 'title', 'creator', 'type', 'created',
                                 'identifier', 'subject', 'coverage', 
                                 'rights', 'source' );


        foreach($dcElementNames as $elementName) {
            if (in_array($elementName, array_keys($this->elements))) {
                foreach($this->elements[$elementName] as $elem)
                    echo "\t<dcterms:$elementName>$elem</dcterms:$elementName>\n";
            } else {
                 echo "\t<dcterms:$elementName/>\n";
            }

        }
        
    ?>  
    <dcterms:source>OMEKA</dcterms:source>     

    <nkl:inCollection><?php echo $this->nakala_collection ?></nkl:inCollection>
    <nkl:relation type="http://purl.org/dc/terms/isVersionOf">11280/9213349d</nkl:relation>
    <nkl:relation type="http://purl.org/dc/terms/isrequiredby">11280/9213349d</nkl:relation>
</nkl:Data>