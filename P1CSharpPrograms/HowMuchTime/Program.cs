using System;

namespace HowMuchTime
{
    class Program
    {
        const int numSecondsInMinute = 60;
        const int numMinutesInHour = 60;
        static void Main(string[] args)
        {
            int numHours, numMinutes, numSeconds;
            
            Console.WriteLine("This program converts seconds into hours, minutes and seconds");

            Console.Write("Enter the number of seconds: ");
            numSeconds = Int32.Parse(Console.ReadLine());

            numHours = numSeconds/(numSecondsInMinute*numMinutesInHour);
            numSeconds %= numSecondsInMinute*numMinutesInHour;
            numMinutes = numSeconds/numSecondsInMinute;
            numSeconds %= numSecondsInMinute;

            Console.WriteLine("This corresponds to {0} hours, {1} minutes, and {2} seconds.", numHours, numMinutes, numSeconds);
        }
    }
}
