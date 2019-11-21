using System;

namespace HowMuchMoney
{
    class Program
    {
        const double m_quarterValue = .25;
        const double m_dimeValue = .10;
        const double m_nickelValue = .05;
        const double m_pennyValue = .01;

        static void Main(string[] args)
        {
            int quarters, dimes, nickels, pennies;
            double total;

            Console.Write("Quarters: ");
            quarters = Int32.Parse(Console.ReadLine());

            Console.Write("Dimes: ");
            dimes = Int32.Parse(Console.ReadLine());
            
            Console.Write("Nickels: ");
            nickels = Int32.Parse(Console.ReadLine());
            
            Console.Write("Pennies: ");
            pennies = Int32.Parse(Console.ReadLine());


            total = m_quarterValue * quarters +
                    m_dimeValue * dimes +
                    m_nickelValue * nickels +
                    m_pennyValue * pennies;
                    
            Console.WriteLine("");
            Console.WriteLine("The total is ${0:F2}", total);
        }
    }
}
