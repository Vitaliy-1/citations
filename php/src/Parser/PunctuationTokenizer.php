<?php namespace CitParser;

use Phpml\Tokenization\Tokenizer;

class PunctuationTokenizer implements Tokenizer {

	public function tokenize(string $text): array {

		return preg_split('/[\s,.:;()]+/u', $text, $limit = -1, PREG_SPLIT_NO_EMPTY);
	}
}

