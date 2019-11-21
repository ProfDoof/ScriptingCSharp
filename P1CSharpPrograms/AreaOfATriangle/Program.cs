using System;

namespace AreaOfATriangle
{
    class Program
    {
        static void Main(string[] args)
        {
            float baseTriangle, heightTriangle;

            Console.WriteLine("This program computes the area of a triangle.");
            Console.WriteLine("");

            Console.Write("Enter the base of the triangle: ");
            baseTriangle = float.Parse(Console.ReadLine());

            Console.Write("Enter the height of the triangle: ");
            heightTriangle = float.Parse(Console.ReadLine());

            Console.WriteLine("");
            Console.WriteLine("The area is {0}", .5*baseTriangle*heightTriangle);
        }
    }
}
