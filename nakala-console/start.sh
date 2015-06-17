#!/bin/bash

echo "Enter packets folder location [press enter for "input"]:"
read INPUT_FOLDER
if [ "$INPUT_FOLDER" = "" ]; then
    INPUT_FOLDER="input"
fi

echo "Enter output folder location [press enter for "output"]:"
read OUTPUT_FOLDER
if [ "$OUTPUT_FOLDER" = "" ]; then
    OUTPUT_FOLDER="output"
fi

echo "Enter error folder location [press enter for "error"]:"
read ERROR_FOLDER
if [ "$ERROR_FOLDER" = "" ]; then
    ERROR_FOLDER="error"
fi

while true; do
    echo "Include facile validation on server (Y/N)?"
    read FACILE
    if [ "$FACILE" = "Y" ]; then
        FACILE="-facileValidation"
        break
    fi
    if [ "$FACILE" = "y" ]; then
        FACILE="-facileValidation"
        break
    fi
    if [ "$FACILE" = "n" ]; then
        FACILE=""
        break
    fi
    if [ "$FACILE" = "N" ]; then
        FACILE=""
        break
    fi
done

while true; do
    echo "Enter email address:"
    read EMAIL
    if [ "$EMAIL" != "" ]; then
		break
    fi
done

java -jar nakala-console.jar -email $EMAIL -inputFolder $INPUT_FOLDER -outputFolder $OUTPUT_FOLDER -errorFolder $ERROR_FOLDER $FACILE

read -p "Press [Enter] key to continue..."