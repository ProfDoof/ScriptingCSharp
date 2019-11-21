using System;

namespace AfterTheDecimal
{
    class Program
    {
        static void Main(string[] args)
        {
            float nonnegativeFloat;

            Console.Write("Enter number: ");
            nonnegativeFloat = float.Parse(Console.ReadLine());

            float answer = nonnegativeFloat - ((int) nonnegativeFloat);
            Console.WriteLine("");
            Console.WriteLine("After the decimal: {0}", answer);
        }
    }
}
