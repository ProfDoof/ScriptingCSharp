using System;

namespace AddTwoIntegers
{
    class Program
    {
        public static void Main(string[] args)
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

    class Athene
    {
        private Console m_console;
        public Athene(System.Console console)
        {
            m_console = console;
        }

        public ReadLine()
        {
            var line = m_console.ReadLine();
            System.WriteLine("<span class=input>{0}</span>", line);
            return line;
        }

        static void Main(string[] args)
        {
            System.Console = Athene(System.Console);
            Program.Main(args);
        }
    }

}
