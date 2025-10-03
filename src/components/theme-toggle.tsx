import { Moon, Sun } from "lucide-react"
import { useTheme } from "@/components/theme-provider"
import { motion } from "framer-motion"

export function ThemeToggle() {
  const { theme, setTheme } = useTheme()
  const isDark = theme === "dark"

  return (
    <button
      onClick={() => setTheme(isDark ? "light" : "dark")}
      className="relative h-12 w-24 rounded-full p-1 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-youtube-red focus:ring-offset-2"
      style={{
        backgroundColor: isDark ? "#1e293b" : "#fbbf24"
      }}
      aria-label="Toggle theme"
    >
      {/* Toggle slider */}
      <motion.div
        className="absolute top-1 flex h-10 w-10 items-center justify-center rounded-full shadow-lg"
        style={{
          backgroundColor: isDark ? "#0f172a" : "#ffffff"
        }}
        animate={{
          x: isDark ? 52 : 0
        }}
        transition={{
          type: "spring",
          stiffness: 300,
          damping: 25
        }}
      >
        <motion.div
          animate={{
            rotate: isDark ? 0 : 360,
            scale: isDark ? 1 : 1.1
          }}
          transition={{
            duration: 0.4
          }}
        >
          {isDark ? (
            <Moon className="h-5 w-5 text-yellow-300" />
          ) : (
            <Sun className="h-5 w-5 text-yellow-500" />
          )}
        </motion.div>
      </motion.div>

      {/* Background icons */}
      <div className="flex h-full items-center justify-between px-3">
        <motion.div
          animate={{
            opacity: isDark ? 0 : 1,
            scale: isDark ? 0.8 : 1
          }}
          transition={{ duration: 0.3 }}
        >
          <Sun className="h-4 w-4 text-yellow-600" />
        </motion.div>
        <motion.div
          animate={{
            opacity: isDark ? 1 : 0,
            scale: isDark ? 1 : 0.8
          }}
          transition={{ duration: 0.3 }}
        >
          <Moon className="h-4 w-4 text-slate-300" />
        </motion.div>
      </div>
    </button>
  )
}