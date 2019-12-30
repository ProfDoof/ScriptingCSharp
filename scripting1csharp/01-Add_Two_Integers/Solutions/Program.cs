using System;

namespace AddTwoIntegers
{
    class Program
    {
        static void Main(string[] args)
        {
            int firstValue, secondValue;

            Console.WriteLine("This program adds two numbers.");

            Console.Write("1st number? ");
            firstValue = Int32.Parse(Console.ReadLine());

            Console.Write("2nd number? ");
            secondValue = Int32.Parse(Console.ReadLine());

            Console.WriteLine("The total is {0}.", firstValue+secondValue);
        }
    }
}