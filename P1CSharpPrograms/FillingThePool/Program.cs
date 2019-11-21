using System;

namespace FillingThePool
{
    class Program
    {
        static void Main(string[] args)
        {
            int length, width, depth, waterEntryRate;
            double volumeOfPool;

            Console.WriteLine("Enter pool dimensions");

            Console.Write("Length: ");
            length = Int32.Parse(Console.ReadLine());

            Console.Write("Width: ");
            width = Int32.Parse(Console.ReadLine());

            Console.Write("Depth: ");
            depth = Int32.Parse(Console.ReadLine());

            Console.WriteLine("");

            Console.Write("Water entry rate: ");
            waterEntryRate = Int32.Parse(Console.ReadLine());

            volumeOfPool = length * width * depth * 7.48;

            Console.WriteLine("");
            Console.WriteLine("The pool will fill completely in {0} minutes", volumeOfPool/waterEntryRate);
        }
    }
}
