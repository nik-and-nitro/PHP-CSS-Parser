<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;

/**
 * The root CSSList of a parsed file. Contains all top-level css contents, mostly declaration blocks,
 * but also any @-rules encountered.
 */
class Document extends CSSBlockList
{
    /**
     * Document constructor.
     *
     * @param int $iLineNo
     */
    public function __construct($iLineNo = 0)
    {
        parent::__construct($iLineNo);
    }

    /**
     * @param ParserState $oParserState
     *
     * @return Document
     *
     * @throws SourceException
     */
    public static function parse(ParserState $oParserState)
    {
        $oDocument = new Document($oParserState->currentLine());
        CSSList::parseList($oParserState, $oDocument);
        return $oDocument;
    }

    /**
     * Gets all DeclarationBlock objects recursively.
     */
    public function getAllDeclarationBlocks()
    {
        $aResult = [];
        $this->allDeclarationBlocks($aResult);
        return $aResult;
    }

    /**
     * @deprecated use getAllDeclarationBlocks()
     */
    public function getAllSelectors()
    {
        return $this->getAllDeclarationBlocks();
    }

    /**
     * Returns all RuleSet objects found recursively in the tree.
     */
    public function getAllRuleSets()
    {
        $aResult = [];
        $this->allRuleSets($aResult);
        return $aResult;
    }

    /**
     * Returns all Value objects found recursively in the tree.
     *
     * @param object|string $mElement
     *        the CSSList or RuleSet to start the search from (defaults to the whole document).
     *        If a string is given, it is used as rule name filter.
     * @param bool $bSearchInFunctionArguments whether to also return Value objects used as Function arguments.
     *
     * @see RuleSet->getRules()
     */
    public function getAllValues($mElement = null, $bSearchInFunctionArguments = false)
    {
        $sSearchString = null;
        if ($mElement === null) {
            $mElement = $this;
        } elseif (is_string($mElement)) {
            $sSearchString = $mElement;
            $mElement = $this;
        }
        $aResult = [];
        $this->allValues($mElement, $aResult, $sSearchString, $bSearchInFunctionArguments);
        return $aResult;
    }

    /**
     * Returns all Selector objects found recursively in the tree.
     * Note that this does not yield the full DeclarationBlock that the selector belongs to
     * (and, currently, there is no way to get to that).
     *
     * @param string $sSpecificitySearch
     *        An optional filter by specificity.
     *        May contain a comparison operator and a number or just a number (defaults to "==").
     *
     * @example getSelectorsBySpecificity('>= 100')
     */
    public function getSelectorsBySpecificity($sSpecificitySearch = null)
    {
        $aResult = [];
        $this->allSelectors($aResult, $sSpecificitySearch);
        return $aResult;
    }

    /**
     * Expands all shorthand properties to their long value
     */
    public function expandShorthands()
    {
        foreach ($this->getAllDeclarationBlocks() as $oDeclaration) {
            $oDeclaration->expandShorthands();
        }
    }

    /**
     * Create shorthands properties whenever possible
     */
    public function createShorthands()
    {
        foreach ($this->getAllDeclarationBlocks() as $oDeclaration) {
            $oDeclaration->createShorthands();
        }
    }

    /**
     * Override render() to make format argument optional
     *
     * @param OutputFormat|null $oOutputFormat
     *
     * @return string
     */
    public function render(OutputFormat $oOutputFormat = null)
    {
        if ($oOutputFormat === null) {
            $oOutputFormat = new OutputFormat();
        }
        return parent::render($oOutputFormat);
    }

    public function isRootList()
    {
        return true;
    }
}