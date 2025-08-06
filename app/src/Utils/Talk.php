<?php

declare(strict_types=1);

namespace App\Utils;

class Talk
{
    private static array $goodbyeInputSet = [
        "bye" => true,
        "exit" => true,
        "goodbye" => true,
        "quit" => true,
    ];

    private static array $goodbyeResponses = [
        "Goodbye. It was nice talking to you.",
        "Thank you for talking with me.",
        "Thank you, that will be \$150. Have a good day!",
        "Goodbye. This was really a nice talk.",
        "Goodbye. I'm looking forward to our next session.",
        "This was a good session, wasn't it – but time is over now. Goodbye.",
        "Maybe we could discuss this over more in our next session? Goodbye.",
        "Good-bye.",
    ];

    private static array $requestInputRegexToResponseOptions = [
        "/i need (.*)/" => [
            "Why do you need %s?",
            "Would it really help you to get %s?",
            "Are you sure you need %s?",
        ],
        '/why don\'?t you ([^\?]*)\??/' => [
            "Do you really think I don't %s?",
            "Perhaps eventually I will %s.",
            "Do you really want me to %s?",
        ],
        '/why can\'?t i ([^\?]*)\??/' => [
            "Do you think you should be able to %s?",
            "If you could %s, what would you do?",
            "I don't know -- why can't you %s?",
            "Have you really tried?",
        ],
        '/i can\'?t (.*)/' => [
            "How do you know you can't %s?",
            "Perhaps you could %s if you tried.",
            "What would it take for you to %s?",
        ],
        "/i am (.*)/" => [
            "Did you come to me because you are %s?",
            "How long have you been %s?",
            "How do you feel about being %s?",
        ],
        '/i\'?m (.*)/' => [
            "How does being %s make you feel?",
            "Do you enjoy being %s?",
            "Why do you tell me you're %s?",
            "Why do you think you're %s?",
        ],
        "/are you ([^\?]*)\??/" => [
            "Why does it matter whether I am %s?",
            "Would you prefer it if I were not %s?",
            "Perhaps you believe I am %s.",
            "I may be %s -- what do you think?",
        ],
        "/what (.*)/" => [
            "Why do you ask?",
            "How would an answer to that help you?",
            "What do you think?",
        ],
        "/how (.*)/" => [
            "How do you suppose?",
            "Perhaps you can answer your own question.",
            "What is it you're really asking?",
        ],
        "/because (.*)/" => [
            "Is that the real reason?",
            "What other reasons come to mind?",
            "Does that reason apply to anything else?",
            "If %s, what else must be true?",
        ],
        "/(.*) sorry (.*)/" => [
            "There are many times when no apology is needed.",
            "What feelings do you have when you apologize?",
        ],
        "/^hello(.*)/" => [
            "Hello...I'm glad you could drop by today.",
            "Hello there...how are you today?",
            "Hello, how are you feeling today?",
        ],
        "/^hi(.*)/" => [
            "Hello...I'm glad you could drop by today.",
            "Hi there...how are you today?",
            "Hello, how are you feeling today?",
        ],
        "/^thanks(.*)/" => ["You're welcome!", "Anytime!"],
        "/^thank you(.*)/" => ["You're welcome!", "Anytime!"],
        "/^good morning(.*)/" => [
            "Good morning...I'm glad you could drop by today.",
            "Good morning...how are you today?",
            "Good morning, how are you feeling today?",
        ],
        "/^good afternoon(.*)/" => [
            "Good afternoon...I'm glad you could drop by today.",
            "Good afternoon...how are you today?",
            "Good afternoon, how are you feeling today?",
        ],
        "/i think (.*)/" => [
            "Do you doubt %s?",
            "Do you really think so?",
            "But you're not sure %s?",
        ],
        "/(.*) friend (.*)/" => [
            "Tell me more about your friends.",
            "When you think of a friend, what comes to mind?",
            "Why don't you tell me about a childhood friend?",
        ],
        "/yes/" => ["You seem quite sure.", "OK, but can you elaborate a bit?"],
        "/(.*) computer(.*)/" => [
            "Are you really talking about me?",
            "Does it seem strange to talk to a computer?",
            "How do computers make you feel?",
            "Do you feel threatened by computers?",
        ],
        "/is it (.*)/" => [
            "Do you think it is %s?",
            "Perhaps it's %s -- what do you think?",
            "If it were %s, what would you do?",
            "It could well be that %s.",
        ],
        "/it is (.*)/" => [
            "You seem very certain.",
            "If I told you that it probably isn't %s, what would you feel?",
        ],
        "/can you ([^\?]*)\??/" => [
            "What makes you think I can't %s?",
            "If I could %s, then what?",
            "Why do you ask if I can %s?",
        ],
        "/(.*)dream(.*)/" => ["Tell me more about your dream."],
        "/can i ([^\?]*)\??/" => [
            "Perhaps you don't want to %s.",
            "Do you want to be able to %s?",
            "If you could %s, would you?",
        ],
        "/you are (.*)/" => [
            "Why do you think I am %s?",
            "Does it please you to think that I'm %s?",
            "Perhaps you would like me to be %s.",
            "Perhaps you're really talking about yourself?",
        ],
        '/you\'?re (.*)/' => [
            "Why do you say I am %s?",
            "Why do you think I am %s?",
            "Are we talking about you, or me?",
        ],
        '/i don\'?t (.*)/' => [
            "Don't you really %s?",
            "Why don't you %s?",
            "Do you want to %s?",
        ],
        "/i feel (.*)/" => [
            "Good, tell me more about these feelings.",
            "Do you often feel %s?",
            "When do you usually feel %s?",
            "When you feel %s, what do you do?",
            "Feeling %s? Tell me more.",
        ],
        "/i have (.*)/" => [
            "Why do you tell me that you've %s?",
            "Have you really %s?",
            "Now that you have %s, what will you do next?",
        ],
        "/i would (.*)/" => [
            "Could you explain why you would %s?",
            "Why would you %s?",
            "Who else knows that you would %s?",
        ],
        "/is there (.*)/" => [
            "Do you think there is %s?",
            "It's likely that there is %s.",
            "Would you like there to be %s?",
        ],
        "/my (.*)/" => [
            "I see, your %s.",
            "Why do you say that your %s?",
            "When your %s, how do you feel?",
        ],
        "/you (.*)/" => [
            "We should be discussing you, not me.",
            "Why do you say that about me?",
            "Why do you care whether I %s?",
        ],
        "/why (.*)/" => [
            "Why don't you tell me the reason why %s?",
            "Why do you think %s?",
        ],
        "/i want (.*)/" => [
            "What would it mean to you if you got %s?",
            "Why do you want %s?",
            "What would you do if you got %s?",
            "If you got %s, then what would you do?",
        ],
        "/(.*) mother(.*)/" => [
            "Tell me more about your mother.",
            "What was your relationship with your mother like?",
            "How do you feel about your mother?",
            "How does this relate to your feelings today?",
            "Good family relations are important.",
        ],
        "/(.*) father(.*)/" => [
            "Tell me more about your father.",
            "How did your father make you feel?",
            "How do you feel about your father?",
            "Does your relationship with your father relate to your feelings today?",
            "Do you have trouble showing affection with your family?",
        ],
        "/(.*) child(.*)/" => [
            "Did you have close friends as a child?",
            "What is your favorite childhood memory?",
            "Do you remember any dreams or nightmares from childhood?",
            "Did the other children sometimes tease you?",
            "How do you think your childhood experiences relate to your feelings today?",
        ],
        "/(.*)\?/" => [
            "Why do you ask that?",
            "Please consider whether you can answer your own question.",
            "Perhaps the answer lies within yourself?",
            "Why don't you tell me?",
        ],
    ];

    private static array $defaultResponses = [
        "Please tell me more.",
        "Let's change focus a bit...Tell me about your family.",
        "Can you elaborate on that?",
        "I see.",
        "Very interesting.",
        "I see. And what does that tell you?",
        "How does that make you feel?",
        "How do you feel when you say that?",
    ];

    private static array $reflectedWords = [
        "am" => "are",
        "was" => "were",
        "i" => "you",
        "i'd" => "you would",
        "i've" => "you have",
        "i'll" => "you will",
        "my" => "your",
        "are" => "am",
        "you've" => "I have",
        "you'll" => "I will",
        "your" => "my",
        "yours" => "mine",
        "you" => "me",
        "me" => "you",
    ];

    private static array $introResponses = [
        "Hi %s. I'm just a greeter.",
        "Before we begin, %s, let me tell you something about myself.",
    ];

    private static array $facts = [
        "I was created by Joseph Weizenbaum.",
        "I was created in the 1960s.",
        "I am a Rogerian psychotherapist.",
        "I am named after Eliza Doolittle from the play Pygmalion.",
        "I was originally written on an IBM 7094.",
        "I can be accessed in most Emacs implementations with the command M-x doctor.",
        "I was created at the MIT Artificial Intelligence Laboratory.",
        "I was one of the first programs capable of attempting the Turing test.",
        "I was designed as a method to show the superficiality of communication between man and machine.",
    ];

    private static function preprocess(string $input): string
    {
        return trim(strtolower(trim($input)), ".!?'\"");
    }

    private static function randomElementFrom(array $list): string
    {
        return $list[array_rand($list)];
    }

    private static function reflect(string $fragment): string
    {
        $words = preg_split("/\s+/", $fragment);
        foreach ($words as $i => $word) {
            $lower = strtolower($word);
            if (isset(self::$reflectedWords[$lower])) {
                $words[$i] = self::$reflectedWords[$lower];
            }
        }
        return implode(" ", $words);
    }

    private static function lookupResponse(string $input): string
    {
        foreach (
            self::$requestInputRegexToResponseOptions
            as $pattern => $responses
        ) {
            if (preg_match($pattern, $input, $matches)) {
                $response = self::randomElementFrom($responses);

                if (strpos($response, "%s") === false) {
                    return $response;
                }

                if (isset($matches[1])) {
                    $fragment = self::reflect($matches[1]);
                    return sprintf($response, $fragment);
                }
            }
        }

        return self::randomElementFrom(self::$defaultResponses);
    }

    /**
     * Main reply function
     */
    public static function reply(string $input): string
    {
        $input = self::preprocess($input);

        if (isset(self::$goodbyeInputSet[$input])) {
            return self::randomElementFrom(self::$goodbyeResponses);
        }

        return self::lookupResponse($input);
    }

    /**
     * Intro responses for a given name
     */
    public static function getIntroResponses(string $name): array
    {
        $intros = [];
        foreach (self::$introResponses as $template) {
            $intros[] = sprintf($template, $name);
        }

        $intros[] = self::randomElementFrom(self::$facts);
        $intros[] = "How are you feeling today?";

        return $intros;
    }
}
