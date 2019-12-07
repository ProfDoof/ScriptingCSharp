using System;

namespace TesterClass
{
    class Program
    {
        static void Main(string[] args)
        {
            System.Console = Athene();
        }
    }

    class Athene
    {
        private System.Console m_console;
        public Athene(System.Console console)
        {
            m_console = console;
        }

        public string ReadLine()
        {
            string line = m_console.ReadLine();
            m_console.WriteLine("<span class=input>{0}</span>", line);
            return line;
        }
    }

    protected abstract class AtheneTextReader : TextReader
    {
        
    }
}
