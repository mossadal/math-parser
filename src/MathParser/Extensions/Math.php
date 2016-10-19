<?php
/*
* @author      Frank Wikström <frank@mossadal.se>
* @copyright   2016 Frank Wikström
* @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
*/

namespace MathParser\Extensions;

class Math {

    /**
    * Private cache for prime sieve
    *
    * @var array int $sieve
    **/
    private static $sieve = array();

    /**
    * Integer factorization
    *
    * Computes an integer factorization of $n using
    * trial division and a cached sieve of computed primes
    *
    * @param type var Description
    **/
    public static function ifactor($n) {

        // max_n = 2^31-1 = 2147483647
        $d = 2;
        $factors = array();
        $dmax = floor(sqrt($n));

        self::$sieve = array_pad(self::$sieve, $dmax, 1);

        do {
            $r = false;
            while ($n % $d == 0) {

                if (array_key_exists($d, $factors)) $factors[$d]++;
                else $factors[$d] = 1;

                $n/=$d;
                $r = true;
            }
            if ($r) {
                $dmax = floor(sqrt($n));
            }
            if ($n > 1) {
                for ($i = $d; $i <= $dmax; $i += $d){
                    self::$sieve[$i]=0;
                }
                do {
                    $d++;
                } while ($d < $dmax && self::$sieve[$d] != 1);

                if ($d > $dmax) {
                    if (array_key_exists($n, $factors)) $factors[$n]++;
                    else $factors[$n] = 1;
                }
            }
        } while ($n > 1 && $d <= $dmax);

        return $factors;

    }


    /**
    * Compute a square free integer factorization: n = pq^2,
    * where p is square free.
    *
    * The function returns an array:
    * [
    *    'square' => q,
    *    'nonSquare' => p
    * ]
    *
    * @param int $n input
    **/
    public static function squareFreeFactorization($n)
    {
        $factors = self::ifactor($n);

        $square = 1;
        $nonSquare = 1;

        foreach ($factors as $prime => $exponent) {

            if ($exponent % 2 == 1) {
                $reducedExponent = ($exponent-1)/2;
                $nonSquare *= $prime;
            } else {
                $reducedExponent = $exponent/2;
            }
            $square *= pow($prime, $reducedExponent);
        }

        return [ 'square' => $square, 'nonSquare' => $nonSquare ];
    }

    public static function root($n)
    {
        $factors = self::ifactor($n);
        $factorCopy = $factors;

        if (count($factors) == 1)  {
            reset($factors);
            $prime = key($factors);
            return [ $prime, $factors[$prime] ];
        }

        $exponent1 = array_shift($factors);
        $exponent2 = array_shift($factors);
        $exponent = self::gcd($exponent1, $exponent2);

        foreach ($factors as $prime => $n) {
            $exponent = self::gcd($exponent, $n);
        }

        if ($exponent == 1) return [ $n, 1 ];

        $x = 1;
        foreach ($factorCopy as $prime => $n)
        {
            $x = $x * pow($prime, $n/$exponent);
        }

        return [$x, $exponent];
    }


    public static function gcd($a, $b)
    {
        $sign = 1;
        if ($a < 0) $sign = -$sign;
        if ($b < 0) $sign = -$sign;

        while ($b != 0)
        {
            $m = $a % $b;
            $a = $b;
            $b = $m;
        }
        return $sign*abs($a);
    }



	public static function logGamma($a) {
		if($a < 0)
			throw new \InvalidArgumentException("Log gamma calls should be >0.");

		if ($a >= 171)	// Lanczos approximation w/ the given coefficients is accurate to 15 digits for 0 <= real(z) <= 171
			return self::logStirlingApproximation($a);
		else
			return log(self::lanczosApproximation($a));
	}

	private static function logStirlingApproximation($x) {
		$t = 0.5*log(2*pi()) - 0.5*log($x) + $x*(log($x))-$x;

		$x2 = $x * $x;
		$x3 = $x2 * $x;
		$x4 = $x3 * $x;

		$err_term = log(1 + (1.0/(12*$x)) + (1.0/(288*$x2)) - (139.0/(51840*$x3))
			- (571.0/(2488320*$x4)));

		$res = $t + $err_term;
		return $res;
	}

	public static function Factorial($num) {
		if ($num < 0) throw new \InvalidArgumentException("Fatorial calls should be >0.");

		$rval=1;
		for ($i = 1; $i <= $num; $i++)
			$rval = $rval * $i;
		return $rval;
	}

	public static function SemiFactorial($num) {
		if ($num < 0) throw new \InvalidArgumentException("Semifactorial calls should be >0.");

		$rval=1;
		while ($num >= 2) {
			$rval =$rval * $num;
			$num = $num-2;
		}
		return $rval;
	}

	private static function lanczosApproximation($x) {
		$g = 7;
		$p = array(0.99999999999980993, 676.5203681218851, -1259.1392167224028,
			771.32342877765313, -176.61502916214059, 12.507343278686905,
			-0.13857109526572012, 9.9843695780195716e-6, 1.5056327351493116e-7);

		if (abs($x - floor($x)) < 1e-16)
		{
			// if we're real close to an integer, let's just compute the factorial integerly.

			if ($x >= 1)
				return self::Factorial($x - 1);
			else
				return INF;
		}
		else
		{
			$x -= 1;

			$y = $p[0];

			for ($i=1; $i < $g+2; $i++)
			{
				$y = $y + $p[$i]/($x + $i);
			}
			$t = $x + $g + 0.5;


			$res_fr = sqrt(2*pi()) * exp((($x+0.5)*log($t))-$t)*$y;

			return $res_fr;
		}
	}
}
