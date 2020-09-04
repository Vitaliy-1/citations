<?php namespace CitParser;

use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\StopWords\English;
use Phpml\Metric\Accuracy;
use Phpml\ModelManager;
use Phpml\Pipeline;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Dataset\FilesDataset;
use Phpml\Tokenization\WordTokenizer;
use CitParser\PunctuationTokenizer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\NGramTokenizer;

include '../../vendor/autoload.php';

$tokenizer = new PunctuationTokenizer();

$xmlPath = '../../../raw/vancouver/journal_article/training/tagged-training.xml';

$dom = new \DOMDocument();
$dom->load($xmlPath);
$referenceEls = $dom->getElementsByTagName("reference");

$labels = [];
$samples = [];
foreach ($referenceEls as $referenceEl) {
	foreach($referenceEl->childNodes as $referenceContent) {
		if ($referenceContent->nodeType === XML_ELEMENT_NODE) {
			$stringSamples = $tokenizer->tokenize($referenceContent->textContent);
			foreach ($stringSamples as $sample) {
				$samples[] = $sample;
				$labels[] = $referenceContent->nodeName;
			}
		}
	}
}

$dataset = new ArrayDataset($samples, $labels);
$split = new StratifiedRandomSplit($dataset, 0.2);

$pipeline = new Pipeline([
	new TokenCountVectorizer(new NGramTokenizer(1, 3), new English()),
], new NaiveBayes());

$pipeline->train($split->getTrainSamples(), $split->getTrainLabels());
$predicted = $pipeline->predict($split->getTestSamples());

echo 'Accuracy: ' . Accuracy::score($split->getTestLabels(), $predicted) . "\n"; //0.6;
