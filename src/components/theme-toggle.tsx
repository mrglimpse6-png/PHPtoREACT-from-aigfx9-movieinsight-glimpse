import { Moon, Sun } from "lucide-react"
import { useTheme } from "@/components/theme-provider"
import { motion } from "framer-motion"

export function ThemeToggle() {
  const { theme, setTheme } = useTheme()
  const isDark = theme === "dark"

  return (
    <button
      onClick={() => setTheme(isDark ? "light" : "dark")}
      className="relative h-8 w-16 rounded-full p-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-youtube-red focus:ring-offset-2 hover:scale-105 active:scale-95"
      style={{
        backgroundColor: isDark ? "#1e293b" : "#fbbf24",
        boxShadow: isDark
          ? "0 2px 8px rgba(30, 41, 59, 0.5), inset 0 1px 2px rgba(0, 0, 0, 0.3)"
          : "0 2px 8px rgba(251, 191, 36, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.5)"
      }}
      aria-label={isDark ? "Switch to light mode" : "Switch to dark mode"}
      role="switch"
      aria-checked={isDark}
    >
      {/* Toggle slider */}
      <motion.div
        className="absolute top-0.5 flex h-7 w-7 items-center justify-center rounded-full shadow-lg"
        style={{
          backgroundColor: isDark ? "#0f172a" : "#ffffff",
          boxShadow: "0 2px 8px rgba(0, 0, 0, 0.2)"
        }}
        animate={{
          x: isDark ? 32 : 0
        }}
        transition={{
          type: "spring",
          stiffness: 500,
          damping: 30
        }}
      >
        <motion.div
          animate={{
            rotate: isDark ? 360 : 0,
            scale: [1, 1.2, 1]
          }}
          transition={{
            duration: 0.5,
            ease: "easeInOut"
          }}
        >
          {isDark ? (
            <Moon className="h-4 w-4 text-blue-300" fill="currentColor" />
          ) : (
            <Sun className="h-4 w-4 text-yellow-500" />
          )}
        </motion.div>
      </motion.div>

      {/* Background icons with enhanced animation */}
      <div className="flex h-full items-center justify-between px-2">
        <motion.div
          animate={{
            opacity: isDark ? 0 : 1,
            scale: isDark ? 0.5 : 1,
            rotate: isDark ? -90 : 0
          }}
          transition={{ duration: 0.4, ease: "easeInOut" }}
        >
          <Sun className="h-3 w-3 text-yellow-700" />
        </motion.div>
        <motion.div
          animate={{
            opacity: isDark ? 1 : 0,
            scale: isDark ? 1 : 0.5,
            rotate: isDark ? 0 : 90
          }}
          transition={{ duration: 0.4, ease: "easeInOut" }}
        >
          <Moon className="h-3 w-3 text-slate-400" />
        </motion.div>
      </div>

      {/* Subtle stars animation for dark mode */}
      {isDark && (
        <>
          <motion.div
            className="absolute top-1.5 right-2 w-0.5 h-0.5 bg-white rounded-full"
            animate={{
              opacity: [0, 1, 0],
              scale: [0, 1, 0]
            }}
            transition={{
              duration: 2,
              repeat: Infinity,
              repeatDelay: 1
            }}
          />
          <motion.div
            className="absolute top-3 right-4 w-0.5 h-0.5 bg-white rounded-full"
            animate={{
              opacity: [0, 1, 0],
              scale: [0, 1, 0]
            }}
            transition={{
              duration: 2,
              repeat: Infinity,
              repeatDelay: 1.5,
              delay: 0.5
            }}
          />
        </>
      )}
    </button>
  )
}