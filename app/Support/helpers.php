<?php

use App\Support\Constants;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

function startTimer()
{
    Timer::$starttime = microtime(true);
}

function endTimer()
{
    Timer::$endtime = microtime(true);

    return Timer::$endtime - Timer::$starttime;
}

if (!function_exists('studly')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     * @return string
     */
    function studly($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }
}

function toBoolean($boolean)
{
    return $boolean === 'true' ||
        $boolean === '1' ||
        $boolean === 1 ||
        $boolean === true;
}

function extract_credentials(Request $request)
{
    $credentials = $request->only(['email', 'password']);

    $credentials['username'] = $credentials['email'];

    return $credentials;
}

function subsystem_is($subsystem)
{
    return \Session::get('subsystem') === $subsystem;
}

function is_administrator()
{
    if (!($user = Auth::user())) {
        return false;
    }

    return $user->is_administrator;
}

function only_numbers($string)
{
    return preg_replace('/\D/', '', $string);
}

function remove_punctuation($string)
{
    return preg_replace('/[^a-z0-9]+/i', '', $string);
}

function request_data()
{
    return coollect(request()->all());
}

function ld($info)
{
    info($info);
    die();
}

function make_pdf_filename($baseName)
{
    return Str::slug($baseName . ' ' . now()->format('Y m d H i')) . '.pdf';
}

function extract_info_from_mailgun_webhook($data)
{
    return [
        'timestamp' => Carbon::createFromTimestamp(
            Arr::get($data, 'signature.timestamp')
        ),

        'message_id' => array_get(
            $data,
            'event-data.message.headers.message-id'
        ),
    ];
}

function current_user()
{
    return auth()->user();
}

function unnacent($string)
{
    $table = array(
        'Š' => 'S',
        'š' => 's',
        'Đ' => 'Dj',
        'đ' => 'dj',
        'Ž' => 'Z',
        'ž' => 'z',
        'Č' => 'C',
        'č' => 'c',
        'Ć' => 'C',
        'ć' => 'c',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y',
        'Ŕ' => 'R',
        'ŕ' => 'r',
    );

    return strtr($string, $table);
}

function make_slug($string)
{
    return Str::slug(unnacent($string));
}

function capitalizeBrazilian($name)
{
    $string = mb_convert_case($name, MB_CASE_TITLE);

    $string = trim(preg_replace('/\s\s+/', ' ', $string));

    coollect(['de', 'da', 'do', 'das', 'dos', 'e'])->each(function (
        $exception
    ) use (&$string) {
        $exception = mb_convert_case($exception, MB_CASE_TITLE);

        $newCase = mb_convert_case($exception, MB_CASE_LOWER);

        $string = str_replace(" {$exception} ", " {$newCase} ", $string);

        preg_match_all('/(.\'.)/ui', $string, $matched);

        if (isset($matched[0])) {
            coollect($matched[0])->each(function ($match) use (&$string) {
                $newCase = mb_convert_case($match, MB_CASE_UPPER);

                $string = str_replace(
                    substr($match, 1),
                    substr($newCase, 1),
                    $string
                );
            });
        }
    });

    return $string;
}

function permission_slug($string)
{
    $string = str_replace(':', $replace = 'xxxxxxxxxx', $string);

    return str_replace($replace, ':', Str::slug($string));
}

function to_reais($number)
{
    return 'R$ ' . number_format($number, 2, ',', '.');
}

function get_current_department_id()
{
    return auth()->user() && auth()->user()->department
        ? auth()->user()->department->id
        : null;
}

function db_listen($dump = false)
{
    \DB::listen(function ($query) use ($dump) {
        \Log::info($query->sql);
        \Log::info($query->bindings);

        if ($dump) {
            dump($query->sql);
            dump($query->bindings);
        }
    });
}

function faker()
{
    return \Faker\Factory::create('pt_BR');
}

function in($needle, ...$haystack): bool
{
    foreach ($haystack as $hay) {
        if ((is_array($hay) && in($needle, ...$hay)) || $needle == $hay) {
            return true;
        }
    }

    return false;
}

function nin($needle, ...$haystack): bool
{
    return !in($needle, ...$haystack);
}

function allows($ability)
{
    if (!($can = \Illuminate\Support\Facades\Gate::allows($ability))) {
        //    if (!$can = auth()->user()->can($ability)) {
        info(
            sprintf(
                'User [%s, %s] is not permitted to do %s',
                auth()->user()->name,
                auth()->user()->email,
                collect($ability)->implode(', ')
            )
        );
    }

    return $can;
}

function make_deep_path($nameHash, $length = 4)
{
    $deepPath = '';

    for ($i = 1; $i <= $length; $i++) {
        $deepPath =
            $deepPath . substr($nameHash, $i - 1, 1) . DIRECTORY_SEPARATOR;
    }

    return $deepPath;
}

function formMode($mode = null)
{
    return $mode
        ? session()->flash(Constants::SESSION_FORM_MODE, $mode)
        : session(Constants::SESSION_FORM_MODE, Constants::FORM_MODE_SHOW);
}

function is_at_least_verbose($command)
{
    return $command->getOutput()->getVerbosity() >=
        \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE;
}

function is_br_date($date)
{
    try {
        $date = Carbon::createFromFormat('d/m/Y', $date);
    } catch (\InvalidArgumentException $e) {
        //Not a date
        return false;
    }
    return true;
}

class Timer
{
    public static $starttime;
    public static $endtime;
}
