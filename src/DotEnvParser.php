<?php

namespace PHPServerless;

class DotEnvParser {

    /**
     * .env file to array conversion
     *
     * @param $envPath
     * @return array
     */
    public static function envToArray($envPath) {
        $variables = [];
        $content = file_get_contents($envPath);
        $lines = explode("\n", $content);
        if (!$lines) {
            return [];
        }

        foreach ($lines as $line) {
            if ($line === "") {
                continue;
            }

            // Find position of first equals symbol
            $equalsLocation = strpos($line, '=');

            // Pull everything to the left of the first equals
            $key = substr($line, 0, $equalsLocation);

            if (trim($key) == "") {
                continue;
            }

            // Pull everything to the right from the equals to end of the line
            $value = substr($line, ($equalsLocation + 1), strlen($line));

            $variables[trim($key)] = trim(trim($value), '"');
        }

        return $variables;
    }

    /**
     * Array to .env file storage
     *
     * @param $array
     * @param $envPath
     */
    public static function arrayToEnv($array, $envPath) {
        $env = "";
        $position = 0;
        foreach ($array as $key => $value) {
            $position++;

            // If value is blank, or key is numeric meaning not a blank line, then add entry
            if ($value === "" || is_numeric($key)) {
                $env .= "\n";
                continue;
            }


            // If passed in option is a boolean (true or false) this will normally
            // save as 1 or 0. But we want to keep the value as words.
            if (is_bool($value)) {
                $value = ($value === true) ? "true" : "false";
            }

            // Always convert $key to uppercase
            $env .= strtoupper($key) . '="' . $value . '"';

            // If isn't last item in array add new line to end
            if ($position != count($array)) {
                $env .= "\n";
            }
        }

        file_put_contents($envPath, $env);
    }

}
