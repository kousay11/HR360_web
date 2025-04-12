#[Route('/test-grammar', name: 'test_grammar')]
public function testGrammar(GrammarCheckerService $grammarChecker): Response
{
    $testText = "Je suis une phrase avec une erreur gramatique.";
    $result = $grammarChecker->checkGrammar($testText);
    
    dd($result); // Affiche le résultat brut
}